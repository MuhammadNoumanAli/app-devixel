<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\DailyAttendance;
use App\Models\HR\Shift;
use App\Models\User;
use App\Services\HR\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HrAttendanceController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function index(Request $request)
    {
        if (!auth()->user()->can('hr-attendance-list') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $date = null;
        $month = null;

        $query = DailyAttendance::with(['user.employeeProfile', 'user.latestLoginLog', 'shift', 'regularizer']);

        // Non-admin with self-service only sees their own attendance
        if (!auth()->user()->hasRole('Admin') && !auth()->user()->can('hr-attendance-regularize')) {
            $query->where('user_id', auth()->id());
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('month')) {
            $month = $request->input('month');
            $query->where('work_date', 'like', "{$month}%");
        } elseif ($request->filled('date')) {
            $date = $request->input('date');
            $query->where('work_date', $date);
        } else {
            $date = Carbon::today()->toDateString();
            $query->where('work_date', $date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->orderBy('work_date', 'desc')->paginate(20)->withQueryString();
        $employees = User::whereHas('employeeProfile')->orderBy('name')->get();
        $shifts = Shift::where('is_active', true)->get();

        // Efficient lookup of user login logs matching attendance dates
        $userIds = $attendances->pluck('user_id')->unique();
        $dates = $attendances->pluck('work_date')->map(function($d) {
            return is_string($d) ? substr($d, 0, 10) : $d->toDateString();
        })->unique();

        $loginLogsByEmployeeDate = \App\Models\UserLoginLog::whereIn('user_id', $userIds)
            ->whereIn(\Illuminate\Support\Facades\DB::raw('DATE(login_at)'), $dates)
            ->orderBy('login_at', 'desc')
            ->get()
            ->groupBy(function($item) {
                return $item->user_id . '_' . $item->login_at->toDateString();
            });

        // Check if current user punched in today
        $myTodayAttendance = DailyAttendance::where('user_id', auth()->id())
            ->where('work_date', Carbon::today()->toDateString())
            ->first();

        return view('hr.attendance.index', compact('attendances', 'employees', 'shifts', 'date', 'month', 'myTodayAttendance', 'loginLogsByEmployeeDate'));
    }

    /**
     * Web punch (Thumb / Clock In / Out button from portal).
     */
    public function webPunch(Request $request)
    {
        $userId = auth()->id();
        $result = $this->attendanceService->recordPunch(
            $userId,
            auth()->user()->employeeProfile?->biometric_thumb_id,
            Carbon::now()->toDateTimeString(),
            'web_kiosk'
        );

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Biometric Hardware Device Webhook (ADMS / ZKTeco).
     */
    public function deviceWebhook(Request $request)
    {
        $thumbId = $request->input('biometric_thumb_id') ?? $request->input('pin');
        $punchTime = $request->input('punch_time') ?? Carbon::now()->toDateTimeString();

        if (!$thumbId) {
            return response()->json(['error' => 'Missing biometric thumb id'], 422);
        }

        $result = $this->attendanceService->recordPunch(
            null,
            $thumbId,
            $punchTime,
            'thumb_device',
            $request->all()
        );

        return response()->json($result);
    }

    /**
     * Regularize attendance record by supervisor.
     */
    public function regularize(Request $request, $id)
    {
        if (!auth()->user()->can('hr-attendance-regularize') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'status' => 'required|in:present,late,half_day,absent,compensatory_off',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'waive_penalty' => 'nullable|boolean',
        ]);

        $attendance = DailyAttendance::findOrFail($id);
        $workDate = $attendance->work_date->toDateString();

        $checkInFull = $request->filled('check_in') ? "{$workDate} {$request->check_in}:00" : null;
        $checkOutFull = $request->filled('check_out') ? "{$workDate} {$request->check_out}:00" : null;

        $this->attendanceService->regularize(
            $attendance->id,
            $checkInFull,
            $checkOutFull,
            $request->status,
            auth()->id(),
            $request->boolean('waive_penalty')
        );

        return back()->with('success', 'Attendance regularized successfully.');
    }

    /**
     * View user login security logs with IP address, device, and location tracking.
     */
    public function loginLogs(Request $request)
    {
        if (!auth()->user()->can('hr-attendance-list') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $query = \App\Models\UserLoginLog::with(['user.employeeProfile']);

        if (!auth()->user()->hasRole('Admin') && !auth()->user()->can('hr-attendance-regularize')) {
            $query->where('user_id', auth()->id());
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('login_at', $request->date);
        }

        if ($request->filled('month')) {
            $query->where('login_at', 'like', "{$request->month}%");
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('region', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%")
                  ->orWhere('device', 'like', "%{$search}%")
                  ->orWhere('browser', 'like', "%{$search}%")
                  ->orWhere('platform', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        $logs = $query->latest('login_at')->paginate(25)->withQueryString();
        $employees = User::whereHas('employeeProfile')->orderBy('name')->get();

        return view('hr.attendance.login-logs', compact('logs', 'employees'));
    }
}
