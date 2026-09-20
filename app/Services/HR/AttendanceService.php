<?php

namespace App\Services\HR;

use App\Models\HR\AttendancePunch;
use App\Models\HR\DailyAttendance;
use App\Models\HR\EmployeeProfile;
use App\Models\HR\HrSetting;
use App\Models\HR\RosterException;
use App\Models\HR\Shift;
use App\Models\User;
use Carbon\Carbon;

class AttendanceService
{
    /**
     * Record a thumbprint or web punch and reconcile daily attendance.
     */
    public function recordPunch(?int $userId, ?string $biometricThumbId, ?string $punchTime = null, string $source = 'thumb_device', ?array $rawPayload = null): array
    {
        $punchTime = $punchTime ? Carbon::parse($punchTime) : Carbon::now();

        // Resolve user if not provided but thumb ID is given
        if (!$userId && $biometricThumbId) {
            $profile = EmployeeProfile::where('biometric_thumb_id', $biometricThumbId)->first();
            if ($profile) {
                $userId = $profile->user_id;
            }
        }

        if (!$userId) {
            return [
                'success' => false,
                'message' => 'User could not be identified from biometric ID.',
            ];
        }

        $user = User::with('employeeProfile.shift')->find($userId);
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.',
            ];
        }

        // Deduplication hash
        $dedupHash = md5($userId . '_' . $punchTime->format('Y-m-d H:i:s') . '_' . $source);
        $existing = AttendancePunch::where('dedup_hash', $dedupHash)->first();
        if ($existing) {
            $daily = DailyAttendance::where('user_id', $userId)->where('work_date', $punchTime->toDateString())->first();
            return [
                'success' => true,
                'message' => 'Punch already recorded (duplicate ignored).',
                'punch' => $existing,
                'daily_attendance' => $daily,
            ];
        }

        // Save raw punch
        $punch = AttendancePunch::create([
            'user_id' => $userId,
            'biometric_thumb_id' => $biometricThumbId ?? $user->employeeProfile?->biometric_thumb_id,
            'punch_time' => $punchTime,
            'source' => $source,
            'dedup_hash' => $dedupHash,
            'raw_payload' => $rawPayload,
        ]);

        // Reconcile daily attendance
        $daily = $this->reconcileDailyAttendance($user, $punchTime);

        return [
            'success' => true,
            'message' => 'Punch recorded successfully.',
            'punch' => $punch,
            'daily_attendance' => $daily,
        ];
    }

    /**
     * Reconcile daily attendance for a user and punch timestamp.
     */
    public function reconcileDailyAttendance(User $user, Carbon $punchTime): DailyAttendance
    {
        $profile = $user->employeeProfile;
        $workDate = $punchTime->toDateString();
        $settings = HrSetting::instance();

        // Check if there is a roster exception for this date
        $rosterException = RosterException::where('exception_date', $workDate)
            ->where(function ($q) use ($user) {
                $q->whereNull('user_id')->orWhere('user_id', $user->id);
            })
            ->first();

        // Resolve shift
        $shift = null;
        if ($rosterException && $rosterException->shift_id) {
            $shift = Shift::find($rosterException->shift_id);
        } elseif ($profile && $profile->shift_id) {
            $shift = $profile->shift;
        } else {
            $shift = Shift::where('is_active', true)->first();
        }

        $daily = DailyAttendance::firstOrNew([
            'user_id' => $user->id,
            'work_date' => $workDate,
        ]);

        $baseSalary = $profile ? (float) $profile->base_salary : 0;
        $dailyRate = round($baseSalary / 30, 2);
        $daily->daily_salary_rate = $dailyRate;
        if ($shift) {
            $daily->shift_id = $shift->id;
        }

        if (!$daily->exists || !$daily->check_in) {
            // First punch of the day: Check-in
            $daily->check_in = $punchTime;

            // Calculate lateness if shift is defined
            if ($shift) {
                $shiftStart = Carbon::parse($workDate . ' ' . $shift->start_time);
                $graceMinutes = $shift->grace_minutes ?? $settings->grace_period_minutes ?? 15;
                $graceDeadline = (clone $shiftStart)->addMinutes($graceMinutes);

                if ($punchTime->gt($graceDeadline)) {
                    $lateMinutes = (int) $shiftStart->diffInMinutes($punchTime);
                    $daily->late_minutes = $lateMinutes;
                    $daily->status = 'late';

                    // Calculate penalty deduction: e.g. 25% of daily salary
                    $latePercent = $settings->late_deduction_percent ?? 25.00;
                    $daily->late_deduction_amount = round(($dailyRate * $latePercent) / 100, 2);
                } else {
                    $daily->late_minutes = 0;
                    $daily->status = 'present';
                    $daily->late_deduction_amount = 0.00;
                }
            } else {
                $daily->status = 'present';
            }
        } else {
            // Subsequent punch: Check-out
            $daily->check_out = $punchTime;
        }

        // Check compensatory off
        if ($rosterException && $rosterException->type === 'compensatory_off') {
            $daily->status = 'compensatory_off';
            $daily->late_deduction_amount = 0.00;
        }

        $daily->save();

        return $daily;
    }

    /**
     * Regularize attendance by supervisor / HR admin.
     */
    public function regularize(int $attendanceId, ?string $checkIn, ?string $checkOut, string $status, int $supervisorUserId, bool $waivePenalty = false): DailyAttendance
    {
        $attendance = DailyAttendance::findOrFail($attendanceId);

        if ($checkIn) {
            $attendance->check_in = Carbon::parse($checkIn);
        }
        if ($checkOut) {
            $attendance->check_out = Carbon::parse($checkOut);
        }

        $attendance->status = $status;
        $attendance->is_regularized = true;
        $attendance->regularized_by = $supervisorUserId;

        if ($waivePenalty || in_array($status, ['present', 'compensatory_off', 'holiday'])) {
            $attendance->late_deduction_amount = 0.00;
            $attendance->late_minutes = 0;
        }

        $attendance->save();

        return $attendance;
    }
}
