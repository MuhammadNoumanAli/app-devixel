<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\HrSetting;
use App\Models\HR\Leave;
use App\Models\HR\LeaveType;
use App\Models\HR\PublicHoliday;
use App\Models\HR\RosterException;
use App\Models\HR\Shift;
use App\Models\HR\UserLeaveQuota;
use App\Models\User;
use App\Services\HR\LeaveService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HrLeaveController extends Controller
{
    protected LeaveService $leaveService;

    public function __construct(LeaveService $leaveService)
    {
        $this->leaveService = $leaveService;
    }

    public function index(Request $request)
    {
        if (!auth()->user()->can('hr-leaves-list') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $query = Leave::with(['user.employeeProfile', 'leaveType', 'approver']);

        // ESS: Non-admin without approval permission sees only their own leaves
        if (!auth()->user()->hasRole('Admin') && !auth()->user()->can('hr-leaves-approve')) {
            $query->where('user_id', auth()->id());
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leaves = $query->orderBy('start_date', 'desc')->paginate(15)->withQueryString();

        $leaveTypes = LeaveType::where('is_active', true)->get();
        $employees = User::whereHas('employeeProfile')->orderBy('name')->get();
        $holidays = PublicHoliday::whereYear('holiday_date', Carbon::now()->year)->orderBy('holiday_date')->get();
        $rosterExceptions = RosterException::with(['shift', 'user'])->orderBy('exception_date', 'desc')->take(20)->get();
        $shifts = Shift::where('is_active', true)->get();
        $settings = HrSetting::instance();

        // My Leave Quotas for current year
        $myQuotas = UserLeaveQuota::where('user_id', auth()->id())
            ->where('year', Carbon::now()->year)
            ->with('leaveType')
            ->get();

        return view('hr.leaves.index', compact(
            'leaves', 'leaveTypes', 'employees', 'holidays',
            'rosterExceptions', 'shifts', 'settings', 'myQuotas'
        ));
    }

    public function apply(Request $request)
    {
        if (!auth()->user()->can('hr-leaves-apply') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'leave_type_id' => 'required|exists:hr_leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:500',
        ]);

        $result = $this->leaveService->applyLeave(
            auth()->id(),
            (int) $request->leave_type_id,
            $request->start_date,
            $request->end_date,
            $request->reason
        );

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Admin/HR can add leave on behalf of any employee.
     */
    public function adminApplyLeave(Request $request)
    {
        if (!auth()->user()->can('hr-leaves-approve') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'leave_type_id' => 'required|exists:hr_leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:500',
            'auto_approve' => 'nullable|boolean',
        ]);

        $result = $this->leaveService->applyLeave(
            (int) $request->user_id,
            (int) $request->leave_type_id,
            $request->start_date,
            $request->end_date,
            $request->reason
        );

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        // Auto-approve if admin checked the option
        if ($request->boolean('auto_approve') && $result['leave']) {
            $this->leaveService->approveLeave($result['leave']->id, auth()->id());
            return back()->with('success', 'Leave added and approved for employee successfully.');
        }

        return back()->with('success', 'Leave added on behalf of employee successfully.');
    }

    public function approve($id)
    {
        if (!auth()->user()->can('hr-leaves-approve') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $result = $this->leaveService->approveLeave((int) $id, auth()->id());

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    public function reject($id)
    {
        if (!auth()->user()->can('hr-leaves-approve') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $result = $this->leaveService->rejectLeave((int) $id, auth()->id());

        return back()->with('success', $result['message']);
    }

    public function storeHoliday(Request $request)
    {
        if (!auth()->user()->can('hr-holidays-manage') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'holiday_date' => 'required|date|unique:hr_public_holidays,holiday_date',
            'description' => 'nullable|string|max:255',
        ]);

        PublicHoliday::create($request->only('name', 'holiday_date', 'description'));

        return back()->with('success', 'Public holiday added successfully.');
    }

    public function deleteHoliday($id)
    {
        if (!auth()->user()->can('hr-holidays-manage') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        PublicHoliday::findOrFail($id)->delete();

        return back()->with('success', 'Public holiday deleted.');
    }

    public function storeRosterException(Request $request)
    {
        if (!auth()->user()->can('hr-holidays-manage') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'exception_date' => 'required|date',
            'type' => 'required|in:working_day,compensatory_off,holiday',
            'shift_id' => 'nullable|exists:hr_shifts,id',
            'user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string|max:255',
        ]);

        RosterException::create([
            'exception_date' => $request->exception_date,
            'type' => $request->type,
            'shift_id' => $request->shift_id,
            'user_id' => $request->user_id,
            'notes' => $request->notes,
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Roster exception / working weekend created successfully.');
    }

    public function deleteRosterException($id)
    {
        if (!auth()->user()->can('hr-holidays-manage') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        RosterException::findOrFail($id)->delete();

        return back()->with('success', 'Roster exception removed.');
    }
}
