<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\PayrollCycle;
use App\Models\HR\Payslip;
use App\Services\HR\PayrollService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class HrPayrollController extends Controller
{
    protected PayrollService $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    public function index(Request $request)
    {
        if (!auth()->user()->can('hr-payroll-list') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $cycles = PayrollCycle::with('processor')->orderBy('cycle_code', 'desc')->get();

        $selectedCycleId = $request->input('cycle_id', $cycles->first()?->id);
        $selectedCycle = $selectedCycleId ? PayrollCycle::find($selectedCycleId) : null;

        $payslipsQuery = Payslip::with(['user.employeeProfile', 'cycle']);

        if ($selectedCycle) {
            $payslipsQuery->where('payroll_cycle_id', $selectedCycle->id);
        }

        // ESS: Non-admin without generate/lock permission only sees their own payslip
        if (!auth()->user()->hasRole('Admin') && !auth()->user()->can('hr-payroll-generate')) {
            $payslipsQuery->where('user_id', auth()->id());
        }

        $payslips = $payslipsQuery->paginate(20)->withQueryString();

        return view('hr.payroll.index', compact('cycles', 'selectedCycle', 'payslips'));
    }

    public function generate(Request $request)
    {
        if (!auth()->user()->can('hr-payroll-generate') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'cycle_code' => 'required|date_format:Y-m',
        ]);

        try {
            $cycle = $this->payrollService->generatePayrollCycle($request->cycle_code, auth()->id());
            return redirect()->route('hr.payroll.index', ['cycle_id' => $cycle->id])
                ->with('success', "Payroll cycle {$cycle->cycle_code} generated successfully with {$cycle->payslips()->count()} payslips.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function lockCycle($id)
    {
        if (!auth()->user()->can('hr-payroll-lock') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $cycle = $this->payrollService->lockCycle((int) $id);
            return back()->with('success', "Payroll cycle {$cycle->cycle_code} has been approved and locked.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Slip 1: Normal Payslip with commissions prominently included.
     */
    public function showNormalPayslip($id)
    {
        if (!auth()->user()->can('hr-payslip-normal') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $payslip = Payslip::with(['user.employeeProfile', 'cycle', 'items'])->findOrFail($id);

        // Authorization check: User can only view their own unless they have hr-payroll-list
        if (!auth()->user()->hasRole('Admin') && !auth()->user()->can('hr-payroll-generate') && $payslip->user_id !== auth()->id()) {
            abort(403, 'Unauthorized to view this payslip.');
        }

        return view('hr.payroll.payslip-normal', compact('payslip'));
    }

    /**
     * Slip 2: Detailed Payslip with complete operational audit trail.
     */
    public function showDetailedPayslip($id)
    {
        if (!auth()->user()->can('hr-payslip-detailed') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $payslip = Payslip::with(['user.employeeProfile', 'cycle', 'items'])->findOrFail($id);

        if (!auth()->user()->hasRole('Admin') && !auth()->user()->can('hr-payroll-generate') && $payslip->user_id !== auth()->id()) {
            abort(403, 'Unauthorized to view this payslip.');
        }

        return view('hr.payroll.payslip-detailed', compact('payslip'));
    }

    /**
     * Mark payslip payment status.
     */
    public function markPayment(Request $request, $id)
    {
        if (!auth()->user()->can('hr-payroll-lock') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'payment_status' => 'required|in:unpaid,paid',
            'payment_method' => 'required|in:bank_transfer,cash,cheque',
        ]);

        $payslip = Payslip::findOrFail($id);
        $payslip->update([
            'payment_status' => $request->payment_status,
            'payment_method' => $request->payment_method,
        ]);

        return back()->with('success', 'Payslip payment status updated.');
    }

    /**
     * Export payroll cycle to Excel for corporate bank disbursement.
     */
    public function exportExcel($id)
    {
        if (!auth()->user()->can('hr-payroll-export') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $cycle = PayrollCycle::with(['payslips.user.employeeProfile'])->findOrFail($id);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Payroll ' . $cycle->cycle_code);

        // Header
        $headers = [
            'Emp Code', 'Employee Name', 'Designation', 'Bank Name', 'Account Number',
            'IBAN', 'Base Salary', 'Sales Lead Bonus', 'Dispatcher Target Bonus',
            'Dispatch Commission', 'Gross Earnings', 'Late Deductions',
            'Leave Deductions', 'Loan Deductions', 'Total Deductions', 'Net Payable', 'Status'
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        $row = 2;
        foreach ($cycle->payslips as $ps) {
            $user = $ps->user;
            $profile = $user->employeeProfile;

            $sheet->setCellValue("A{$row}", $profile?->employee_code ?? "EMP-{$user->id}");
            $sheet->setCellValue("B{$row}", $user->name);
            $sheet->setCellValue("C{$row}", $profile?->designation ?? 'Staff');
            $sheet->setCellValue("D{$row}", $profile?->bank_name ?? '-');
            $sheet->setCellValue("E{$row}", $profile?->bank_account_number ?? '-');
            $sheet->setCellValue("F{$row}", $profile?->iban ?? '-');
            $sheet->setCellValue("G{$row}", $ps->base_salary_earned);
            $sheet->setCellValue("H{$row}", $ps->sales_lead_bonus_earned);
            $sheet->setCellValue("I{$row}", $ps->dispatcher_target_bonus_earned);
            $sheet->setCellValue("J{$row}", $ps->dispatcher_load_commission_earned);
            $sheet->setCellValue("K{$row}", $ps->total_earnings);
            $sheet->setCellValue("L{$row}", $ps->late_deductions_total);
            $sheet->setCellValue("M{$row}", $ps->unpaid_leave_deductions_total);
            $sheet->setCellValue("N{$row}", $ps->loan_deductions_total);
            $sheet->setCellValue("O{$row}", $ps->total_deductions);
            $sheet->setCellValue("P{$row}", $ps->net_salary);
            $sheet->setCellValue("Q{$row}", ucfirst($ps->payment_status));

            $row++;
        }

        $filename = "Payroll_Disbursement_{$cycle->cycle_code}.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
