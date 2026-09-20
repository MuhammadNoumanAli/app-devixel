<?php

namespace App\Services\HR;

use App\Models\Carrier;
use App\Models\Dispatch;
use App\Models\HR\DailyAttendance;
use App\Models\HR\EmployeeProfile;
use App\Models\HR\HrSetting;
use App\Models\HR\Leave;
use App\Models\HR\Loan;
use App\Models\HR\LoanInstallment;
use App\Models\HR\PayrollCycle;
use App\Models\HR\Payslip;
use App\Models\HR\PayslipItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    /**
     * Generate or recalculate payroll for a given cycle (e.g. "2026-09").
     */
    public function generatePayrollCycle(string $cycleCode, int $processedByUserId): PayrollCycle
    {
        $startDate = Carbon::parse($cycleCode . '-01')->startOfMonth()->toDateString();
        $endDate = Carbon::parse($cycleCode . '-01')->endOfMonth()->toDateString();

        $cycle = PayrollCycle::firstOrCreate(
            ['cycle_code' => $cycleCode],
            [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'processed_by' => $processedByUserId,
                'status' => 'draft',
            ]
        );

        if ($cycle->isLocked()) {
            throw new \Exception("Cannot recalculate payroll: cycle {$cycleCode} is locked.");
        }

        $settings = HrSetting::instance();
        $activeEmployees = User::whereHas('employeeProfile', function ($q) {
            $q->where('status', 'active');
        })->with('employeeProfile')->get();

        $totalCycleGross = 0;
        $totalCycleNet = 0;

        DB::transaction(function () use ($cycle, $activeEmployees, $settings, $startDate, $endDate, &$totalCycleGross, &$totalCycleNet) {
            foreach ($activeEmployees as $user) {
                $payslip = $this->calculateEmployeePayslip($user, $cycle, $settings, $startDate, $endDate);
                $totalCycleGross += $payslip->total_earnings;
                $totalCycleNet += $payslip->net_salary;
            }

            $cycle->update([
                'total_gross' => $totalCycleGross,
                'total_net' => $totalCycleNet,
            ]);
        });

        return $cycle;
    }

    /**
     * Calculate individual employee payslip.
     */
    public function calculateEmployeePayslip(User $user, PayrollCycle $cycle, HrSetting $settings, string $startDate, string $endDate): Payslip
    {
        $profile = $user->employeeProfile;
        $baseSalary = (float) ($profile?->base_salary ?? 0);
        $dailyRate = round($baseSalary / 30, 2);

        // Delete existing payslip if draft to recalculate
        $payslip = Payslip::firstOrNew([
            'payroll_cycle_id' => $cycle->id,
            'user_id' => $user->id,
        ]);

        if ($payslip->exists) {
            $payslip->items()->delete();
        }

        $items = [];

        // 1. BASE SALARY
        $baseEarned = $baseSalary;
        // Check if employee joined mid-month
        if ($profile && $profile->joining_date && Carbon::parse($profile->joining_date)->between($startDate, $endDate)) {
            $joinDate = Carbon::parse($profile->joining_date);
            $daysWorked = $joinDate->diffInDays(Carbon::parse($endDate)) + 1;
            $baseEarned = round($dailyRate * $daysWorked, 2);
        }

        $items[] = [
            'item_type' => 'earning',
            'code' => 'BASE',
            'description' => 'Monthly Base Salary',
            'amount' => $baseEarned,
            'reference_date' => null,
        ];

        // 2. SALES AGENT LEAD BONUS (> $200 within X days of assignment)
        $salesLeadBonusTotal = 0;
        $carriers = Carrier::where('user_id', $user->id)
            ->whereNotNull('assign_to')
            ->whereNotNull('assigned_at')
            ->get();

        $minLeadAmount = (float) ($settings->qualifying_lead_load_min_amount ?? 200.00);
        $maxLeadDays = (int) ($settings->qualifying_lead_max_days ?? 30);
        $leadBonusUnit = (float) ($settings->sales_agent_lead_bonus_amount ?? 25.00);

        foreach ($carriers as $carrier) {
            $loads = Dispatch::where('mc_number', $carrier->mc_number)
                ->whereBetween('load_date', [$startDate, $endDate])
                ->where('is_cancel', '0')
                ->where('receivable', '>', $minLeadAmount)
                ->get();

            foreach ($loads as $load) {
                $daysDiff = Carbon::parse($carrier->assigned_at)->diffInDays(Carbon::parse($load->load_date));
                if ($daysDiff <= $maxLeadDays) {
                    $salesLeadBonusTotal += $leadBonusUnit;
                    $items[] = [
                        'item_type' => 'earning',
                        'code' => 'SALES_LEAD_BONUS',
                        'description' => "Sales Lead Conversion Bonus: Carrier MC#{$carrier->mc_number} (Load #{$load->load_number}, Amount: \${$load->receivable}, Converted in {$daysDiff} days)",
                        'amount' => $leadBonusUnit,
                        'reference_date' => $load->load_date,
                    ];
                    break; // Conversion bonus earned for this lead
                }
            }
        }

        // 3. DYNAMIC DISPATCHER TARGET BONUS (e.g. > $2000 or $4000)
        $dispatcherTargetBonusTotal = 0;
        if ($profile && (float) $profile->monthly_load_target_amount > 0) {
            $totalLoadsAmount = (float) Dispatch::where('user_id', $user->id)
                ->whereBetween('load_date', [$startDate, $endDate])
                ->where('is_cancel', '0')
                ->sum('receivable');

            if ($totalLoadsAmount >= (float) $profile->monthly_load_target_amount) {
                $bonusCalc = 0;
                if (in_array($profile->target_bonus_type, ['fixed', 'both'])) {
                    $bonusCalc += (float) $profile->target_bonus_fixed;
                }
                if (in_array($profile->target_bonus_type, ['percentage', 'both']) && (float) $profile->target_bonus_percentage > 0) {
                    $bonusCalc += round(($totalLoadsAmount * (float) $profile->target_bonus_percentage) / 100, 2);
                }

                if ($bonusCalc > 0) {
                    $dispatcherTargetBonusTotal = $bonusCalc;
                    $items[] = [
                        'item_type' => 'earning',
                        'code' => 'DISPATCHER_TARGET_BONUS',
                        'description' => "Dispatcher Target Milestone Bonus: Volume \${$totalLoadsAmount} (Target: \${$profile->monthly_load_target_amount})",
                        'amount' => $bonusCalc,
                        'reference_date' => null,
                    ];
                }
            }
        }

        // 4. DISPATCHER LOAD COMMISSION
        $dispatcherCommissionTotal = 0;
        if ($user->load_commission > 0 || $user->commission_percent > 0) {
            $completedLoads = Dispatch::where('user_id', $user->id)
                ->whereBetween('load_date', [$startDate, $endDate])
                ->where('is_cancel', '0')
                ->get();

            foreach ($completedLoads as $load) {
                $comm = 0;
                if ($user->load_commission > 0) {
                    $comm += (float) $user->load_commission;
                }
                if ($user->commission_percent > 0 && $load->receivable > 0) {
                    $comm += round(((float) $load->receivable * (float) $user->commission_percent) / 100, 2);
                }

                if ($comm > 0) {
                    $dispatcherCommissionTotal += $comm;
                }
            }

            if ($dispatcherCommissionTotal > 0) {
                $items[] = [
                    'item_type' => 'earning',
                    'code' => 'DISPATCH_COMMISSION',
                    'description' => "Dispatcher Load Volume Commission (" . count($completedLoads) . " loads)",
                    'amount' => $dispatcherCommissionTotal,
                    'reference_date' => null,
                ];
            }
        }

        // 5. ATTENDANCE & LATE DEDUCTIONS
        $dailyAttendances = DailyAttendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->get();

        $presentDays = 0;
        $lateCount = 0;
        $lateDeductionTotal = 0;

        foreach ($dailyAttendances as $att) {
            if (in_array($att->status, ['present', 'late'])) {
                $presentDays++;
            }
            if ($att->status === 'late' || (float) $att->late_deduction_amount > 0) {
                $lateCount++;
                $dedAmt = (float) $att->late_deduction_amount;
                $lateDeductionTotal += $dedAmt;

                if ($dedAmt > 0) {
                    $items[] = [
                        'item_type' => 'deduction',
                        'code' => 'LATE_PENALTY',
                        'description' => "Late Penalty ({$att->late_minutes}m late - {$settings->late_deduction_percent}% daily deduction)",
                        'amount' => $dedAmt,
                        'reference_date' => $att->work_date,
                    ];
                }
            }
        }

        // 6. LEAVES & EXCESS LEAVE DEDUCTIONS (Max 2 paid leaves per month)
        $approvedLeaves = Leave::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate]);
            })
            ->get();

        $totalApprovedLeaveDays = $approvedLeaves->sum('days_count');
        $maxPaidLeaves = (int) ($settings->max_paid_leaves_per_month ?? 2);
        $paidLeaveDays = min($totalApprovedLeaveDays, $maxPaidLeaves);
        $unpaidLeaveDays = max(0, $totalApprovedLeaveDays - $paidLeaveDays);

        // Also add explicit unpaid leaves
        $explicitUnpaidLeaves = Leave::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('is_paid', false)
            ->whereBetween('start_date', [$startDate, $endDate])
            ->sum('days_count');

        $unpaidLeaveDeductionTotal = 0;
        $totalUnpaidDays = $unpaidLeaveDays + $explicitUnpaidLeaves;

        if ($totalUnpaidDays > 0) {
            $unpaidLeaveDeductionTotal = round($totalUnpaidDays * $dailyRate, 2);
            $items[] = [
                'item_type' => 'deduction',
                'code' => 'LEAVE_DEDUCTION',
                'description' => "Leave Deductions: {$totalUnpaidDays} unpaid day(s) (Exceeds monthly cap of {$maxPaidLeaves} paid leaves or unpaid leave)",
                'amount' => $unpaidLeaveDeductionTotal,
                'reference_date' => null,
            ];
        }

        // 7. LOAN RECOVERIES
        $loanDeductionsTotal = 0;
        $activeLoans = Loan::where('user_id', $user->id)
            ->whereIn('status', ['approved', 'active'])
            ->where('remaining_balance', '>', 0)
            ->get();

        foreach ($activeLoans as $loan) {
            $installment = LoanInstallment::where('loan_id', $loan->id)
                ->where('status', 'pending')
                ->whereBetween('due_date', [$startDate, $endDate])
                ->first();

            if ($installment) {
                $loanDeductionsTotal += (float) $installment->amount;
                $items[] = [
                    'item_type' => 'deduction',
                    'code' => 'LOAN_RECOVERY',
                    'description' => "Loan Installment #{$installment->installment_number} ({$loan->purpose})",
                    'amount' => (float) $installment->amount,
                    'reference_date' => $installment->due_date,
                ];
            }
        }

        // CALCULATE TOTALS
        $totalEarnings = $baseEarned + $salesLeadBonusTotal + $dispatcherTargetBonusTotal + $dispatcherCommissionTotal;
        $totalDeductions = $lateDeductionTotal + $unpaidLeaveDeductionTotal + $loanDeductionsTotal;
        $netSalary = max(0, round($totalEarnings - $totalDeductions, 2));

        $payslip->fill([
            'eligible_work_days' => 30,
            'present_days' => $presentDays,
            'paid_leave_days' => $paidLeaveDays,
            'unpaid_leave_days' => $totalUnpaidDays,
            'late_count' => $lateCount,
            'base_salary_earned' => $baseEarned,
            'sales_lead_bonus_earned' => $salesLeadBonusTotal,
            'dispatcher_target_bonus_earned' => $dispatcherTargetBonusTotal,
            'dispatcher_load_commission_earned' => $dispatcherCommissionTotal,
            'total_earnings' => $totalEarnings,
            'late_deductions_total' => $lateDeductionTotal,
            'unpaid_leave_deductions_total' => $unpaidLeaveDeductionTotal,
            'loan_deductions_total' => $loanDeductionsTotal,
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,
        ]);

        $payslip->save();

        // Create line items
        foreach ($items as $item) {
            $payslip->items()->create($item);
        }

        return $payslip;
    }

    /**
     * Lock a payroll cycle to prevent any further changes.
     */
    public function lockCycle(int $cycleId): PayrollCycle
    {
        $cycle = PayrollCycle::findOrFail($cycleId);
        $cycle->status = 'locked';
        $cycle->save();

        // Mark associated loan installments as deducted
        foreach ($cycle->payslips as $payslip) {
            $loanItems = $payslip->items()->where('code', 'LOAN_RECOVERY')->get();
            foreach ($loanItems as $item) {
                $installment = LoanInstallment::where('payslip_id', $payslip->id)->orWhere(function ($q) use ($payslip, $item) {
                    $q->whereHas('loan', fn($l) => $l->where('user_id', $payslip->user_id))
                        ->where('amount', $item->amount)
                        ->where('status', 'pending');
                })->first();

                if ($installment) {
                    $installment->update([
                        'payslip_id' => $payslip->id,
                        'status' => 'deducted',
                        'paid_date' => Carbon::now()->toDateString(),
                    ]);

                    $loan = $installment->loan;
                    $loan->remaining_balance = max(0, $loan->remaining_balance - $installment->amount);
                    if ($loan->remaining_balance <= 0) {
                        $loan->status = 'repaid';
                    }
                    $loan->save();
                }
            }
        }

        return $cycle;
    }
}
