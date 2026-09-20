<?php

namespace App\Models\HR;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyAttendance extends Model
{
    use HasFactory;

    protected $table = 'hr_daily_attendance';

    protected $fillable = [
        'user_id',
        'work_date',
        'shift_id',
        'check_in',
        'check_out',
        'late_minutes',
        'status',
        'daily_salary_rate',
        'late_deduction_amount',
        'is_regularized',
        'regularized_by',
    ];

    protected $casts = [
        'work_date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'late_minutes' => 'integer',
        'daily_salary_rate' => 'decimal:2',
        'late_deduction_amount' => 'decimal:2',
        'is_regularized' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function regularizer()
    {
        return $this->belongsTo(User::class, 'regularized_by');
    }

    public function loginLog()
    {
        return $this->hasOne(\App\Models\UserLoginLog::class, 'user_id', 'user_id');
    }

    public function getDayLoginLogAttribute()
    {
        if (!$this->work_date || !$this->user_id) {
            return null;
        }
        $dateStr = is_string($this->work_date) ? substr($this->work_date, 0, 10) : $this->work_date->toDateString();
        return \App\Models\UserLoginLog::where('user_id', $this->user_id)
            ->whereDate('login_at', $dateStr)
            ->latest('login_at')
            ->first() ?? $this->user?->latestLoginLog;
    }
}
