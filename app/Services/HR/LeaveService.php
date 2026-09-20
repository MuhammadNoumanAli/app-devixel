<?php

namespace App\Services\HR;

use App\Models\HR\DailyAttendance;
use App\Models\HR\HrSetting;
use App\Models\HR\Leave;
use App\Models\HR\LeaveType;
use App\Models\HR\PublicHoliday;
use App\Models\HR\UserLeaveQuota;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class LeaveService
{
    /**
     * Apply for a leave.
     */
    public function applyLeave(int $userId, int $leaveTypeId, string $startDate, string $endDate, ?string $reason = null): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        if ($end->lt($start)) {
            return ['success' => false, 'message' => 'End date cannot be earlier than start date.'];
        }

        $settings = HrSetting::instance();
        $leaveType = LeaveType::findOrFail($leaveTypeId);
        $weekendDays = $settings->weekend_days ?? ['Sunday'];

        // Calculate actual working days excluding weekends and public holidays
        $period = CarbonPeriod::create($start, $end);
        $holidayDates = PublicHoliday::whereBetween('holiday_date', [$startDate, $endDate])
            ->pluck('holiday_date')
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->toArray();

        $daysCount = 0;
        foreach ($period as $date) {
            $dayName = $date->format('l');
            $dateStr = $date->toDateString();
            if (in_array($dayName, $weekendDays) || in_array($dateStr, $holidayDates)) {
                continue;
            }
            $daysCount++;
        }

        if ($daysCount <= 0) {
            return ['success' => false, 'message' => 'Selected date range falls entirely on weekends or public holidays.'];
        }

        // Determine if paid or unpaid based on type and monthly quota
        $isPaid = $leaveType->is_paid;

        if ($isPaid) {
            // Check monthly paid leave cap (e.g. max 2 paid leaves in a single month)
            $monthStart = $start->copy()->startOfMonth()->toDateString();
            $monthEnd = $start->copy()->endOfMonth()->toDateString();

            $alreadyApprovedDaysThisMonth = Leave::where('user_id', $userId)
                ->where('status', 'approved')
                ->where('is_paid', true)
                ->whereBetween('start_date', [$monthStart, $monthEnd])
                ->sum('days_count');

            $maxPerMonth = $settings->max_paid_leaves_per_month ?? 2;
            if (($alreadyApprovedDaysThisMonth + $daysCount) > $maxPerMonth) {
                // If it exceeds the monthly limit, user is notified that excess days will be unpaid
                // Leave itself will be tracked; during payroll, excess over 2 is deducted
            }
        }

        $leave = Leave::create([
            'user_id' => $userId,
            'leave_type_id' => $leaveTypeId,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'days_count' => $daysCount,
            'is_paid' => $isPaid,
            'reason' => $reason,
            'status' => 'pending',
        ]);

        return [
            'success' => true,
            'message' => 'Leave application submitted successfully.',
            'leave' => $leave,
        ];
    }

    /**
     * Approve leave application.
     */
    public function approveLeave(int $leaveId, int $approverUserId): array
    {
        $leave = Leave::with(['leaveType', 'user.employeeProfile'])->findOrFail($leaveId);

        if ($leave->status === 'approved') {
            return ['success' => false, 'message' => 'Leave has already been approved.'];
        }

        $leave->status = 'approved';
        $leave->approved_by = $approverUserId;
        $leave->approved_at = Carbon::now();
        $leave->save();

        // Update quota if paid
        $year = Carbon::parse($leave->start_date)->year;
        $quota = UserLeaveQuota::firstOrCreate(
            [
                'user_id' => $leave->user_id,
                'leave_type_id' => $leave->leave_type_id,
                'year' => $year,
            ],
            [
                'allocated_days' => $leave->leaveType->default_quota ?? 0,
                'used_days' => 0,
                'remaining_days' => $leave->leaveType->default_quota ?? 0,
            ]
        );

        if ($leave->is_paid) {
            $quota->used_days += $leave->days_count;
            $quota->remaining_days = max(0, $quota->allocated_days - $quota->used_days);
            $quota->save();
        }

        // Mark daily attendance records as on_leave
        $period = CarbonPeriod::create($leave->start_date, $leave->end_date);
        $settings = HrSetting::instance();
        $weekendDays = $settings->weekend_days ?? ['Sunday'];

        foreach ($period as $date) {
            if (in_array($date->format('l'), $weekendDays)) {
                continue;
            }
            DailyAttendance::updateOrCreate(
                [
                    'user_id' => $leave->user_id,
                    'work_date' => $date->toDateString(),
                ],
                [
                    'status' => 'on_leave',
                    'late_minutes' => 0,
                    'late_deduction_amount' => 0.00,
                    'is_regularized' => true,
                    'regularized_by' => $approverUserId,
                ]
            );
        }

        return [
            'success' => true,
            'message' => 'Leave approved successfully.',
            'leave' => $leave,
        ];
    }

    /**
     * Reject leave application.
     */
    public function rejectLeave(int $leaveId, int $approverUserId): array
    {
        $leave = Leave::findOrFail($leaveId);
        $leave->status = 'rejected';
        $leave->approved_by = $approverUserId;
        $leave->approved_at = Carbon::now();
        $leave->save();

        return [
            'success' => true,
            'message' => 'Leave rejected successfully.',
            'leave' => $leave,
        ];
    }
}
