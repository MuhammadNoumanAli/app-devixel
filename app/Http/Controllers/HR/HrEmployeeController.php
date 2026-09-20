<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\EmployeeProfile;
use App\Models\HR\Shift;
use App\Models\User;
use Illuminate\Http\Request;

class HrEmployeeController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('hr-employees-list') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $query = User::with(['employeeProfile.shift', 'roles']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('employeeProfile', function ($ep) use ($search) {
                      $ep->where('employee_code', 'like', "%{$search}%")
                         ->orWhere('designation', 'like', "%{$search}%")
                         ->orWhere('biometric_thumb_id', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->whereHas('employeeProfile', function ($ep) use ($request) {
                $ep->where('status', $request->status);
            });
        }

        $perPage = in_array((int) $request->input('per_page'), [10, 15, 20, 25, 50]) ? (int) $request->input('per_page') : 15;
        $employees = $query->paginate($perPage)->withQueryString();

        return view('hr.employees.index', compact('employees'));
    }

    public function edit(User $user)
    {
        if (!auth()->user()->can('hr-employees-edit') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $profile = $user->employeeProfile ?? new EmployeeProfile(['user_id' => $user->id]);
        $shifts = Shift::where('is_active', true)->get();

        return view('hr.employees.edit', compact('user', 'profile', 'shifts'));
    }

    public function update(Request $request, User $user)
    {
        if (!auth()->user()->can('hr-employees-edit') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'employee_code' => 'nullable|string|max:50|unique:hr_employee_profiles,employee_code,' . ($user->employeeProfile->id ?? 'NULL'),
            'shift_id' => 'nullable|exists:hr_shifts,id',
            'designation' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'joining_date' => 'nullable|date',
            'base_salary' => 'required|numeric|min:0',
            'biometric_thumb_id' => 'nullable|string|max:50|unique:hr_employee_profiles,biometric_thumb_id,' . ($user->employeeProfile->id ?? 'NULL'),
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'iban' => 'nullable|string|max:50',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:50',
            'monthly_load_target_amount' => 'nullable|numeric|min:0',
            'target_bonus_type' => 'required|in:fixed,percentage,both',
            'target_bonus_fixed' => 'nullable|numeric|min:0',
            'target_bonus_percentage' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:active,probation,resigned,terminated',
            // Also allow updating direct load commission
            'load_commission' => 'nullable|numeric|min:0',
            'commission_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        // Update user commissions
        $user->update([
            'load_commission' => $request->load_commission ?? 0,
            'commission_percent' => $request->commission_percent ?? 0,
        ]);

        // Update or create profile
        EmployeeProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'employee_code' => $validated['employee_code'],
                'shift_id' => $validated['shift_id'],
                'designation' => $validated['designation'],
                'department' => $validated['department'] ?? null,
                'joining_date' => $validated['joining_date'],
                'base_salary' => $validated['base_salary'],
                'biometric_thumb_id' => $validated['biometric_thumb_id'],
                'bank_name' => $validated['bank_name'],
                'bank_account_number' => $validated['bank_account_number'],
                'iban' => $validated['iban'],
                'emergency_contact_name' => $validated['emergency_contact_name'],
                'emergency_contact_phone' => $validated['emergency_contact_phone'],
                'monthly_load_target_amount' => $validated['monthly_load_target_amount'] ?? 0,
                'target_bonus_type' => $validated['target_bonus_type'],
                'target_bonus_fixed' => $validated['target_bonus_fixed'] ?? 0,
                'target_bonus_percentage' => $validated['target_bonus_percentage'] ?? 0,
                'status' => $validated['status'],
            ]
        );

        return redirect()->route('hr.employees.index')->with('success', 'Employee profile updated successfully.');
    }
}
