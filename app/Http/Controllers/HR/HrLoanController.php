<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Loan;
use App\Models\HR\LoanInstallment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HrLoanController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('hr-loans-list') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $query = Loan::with(['user.employeeProfile', 'installments', 'approver']);

        // ESS: Non-admin without manage permission only sees their own loans
        if (!auth()->user()->hasRole('Admin') && !auth()->user()->can('hr-loans-manage')) {
            $query->where('user_id', auth()->id());
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $loans = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $employees = User::whereHas('employeeProfile')->orderBy('name')->get();

        return view('hr.loans.index', compact('loans', 'employees'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('hr-loans-manage') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'principal_amount' => 'required|numeric|min:1',
            'monthly_installment' => 'required|numeric|min:1|lte:principal_amount',
            'purpose' => 'nullable|string|max:255',
        ]);

        Loan::create([
            'user_id' => $request->user_id,
            'principal_amount' => $request->principal_amount,
            'monthly_installment' => $request->monthly_installment,
            'remaining_balance' => $request->principal_amount,
            'purpose' => $request->purpose,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Loan request created successfully.');
    }

    public function approve($id)
    {
        if (!auth()->user()->can('hr-loans-manage') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $loan = Loan::findOrFail($id);
        if ($loan->status !== 'pending') {
            return back()->with('error', 'Only pending loans can be approved.');
        }

        $loan->update([
            'status' => 'active',
            'approved_by' => auth()->id(),
            'approved_at' => Carbon::now(),
        ]);

        // Generate installments
        $installmentsCount = (int) ceil($loan->principal_amount / $loan->monthly_installment);
        $currentDate = Carbon::now()->addMonth()->startOfMonth();
        $remaining = $loan->principal_amount;

        for ($i = 1; $i <= $installmentsCount; $i++) {
            $amount = min($loan->monthly_installment, $remaining);
            LoanInstallment::create([
                'loan_id' => $loan->id,
                'installment_number' => $i,
                'amount' => $amount,
                'due_date' => $currentDate->toDateString(),
                'status' => 'pending',
            ]);
            $remaining -= $amount;
            $currentDate->addMonth();
        }

        return back()->with('success', "Loan approved and {$installmentsCount} installments scheduled.");
    }

    public function cancel($id)
    {
        if (!auth()->user()->can('hr-loans-manage') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $loan = Loan::findOrFail($id);
        $loan->update(['status' => 'cancelled']);
        $loan->installments()->where('status', 'pending')->delete();

        return back()->with('success', 'Loan cancelled.');
    }
}
