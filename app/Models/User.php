<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'name',
        'email',
        'avatar',
        'password',
        'status',
        'load_commission',
        'commission_percent',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function carriers()
    {
        return $this->hasMany(Carrier::class);
    }

    public function assignedCarriers()
    {
        return $this->hasMany(Carrier::class, 'assign_to');
    }

    public function dispatchers()
    {
        return $this->hasMany(Dispatch::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'from_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'to_id');
    }

    public function employeeProfile()
    {
        return $this->hasOne(\App\Models\HR\EmployeeProfile::class, 'user_id');
    }

    public function dailyAttendances()
    {
        return $this->hasMany(\App\Models\HR\DailyAttendance::class, 'user_id');
    }

    public function attendancePunches()
    {
        return $this->hasMany(\App\Models\HR\AttendancePunch::class, 'user_id');
    }

    public function leaves()
    {
        return $this->hasMany(\App\Models\HR\Leave::class, 'user_id');
    }

    public function leaveQuotas()
    {
        return $this->hasMany(\App\Models\HR\UserLeaveQuota::class, 'user_id');
    }

    public function payslips()
    {
        return $this->hasMany(\App\Models\HR\Payslip::class, 'user_id');
    }

    public function loans()
    {
        return $this->hasMany(\App\Models\HR\Loan::class, 'user_id');
    }

    public function loginLogs()
    {
        return $this->hasMany(\App\Models\UserLoginLog::class, 'user_id');
    }

    public function latestLoginLog()
    {
        return $this->hasOne(\App\Models\UserLoginLog::class, 'user_id')->latestOfMany('login_at');
    }

    public function getFullNameAttribute()
    {
        if (!empty($this->first_name) || !empty($this->last_name)) {
            return trim($this->first_name . " " . $this->last_name);
        }
        return $this->name ?? 'User';
    }

    public function getNameAttribute($value)
    {
        if (!empty($this->first_name) || !empty($this->last_name)) {
            return trim($this->first_name . " " . $this->last_name);
        }
        return $value ?? 'User';
    }

    public function scopeStatus($query, $type)
    {
        return $query->where('status', $type);
    }

    public function isOnline()
    {
        return \Illuminate\Support\Facades\Cache::has('user-online-' . $this->id);
    }

    public function getAvatarUrlAttribute()
    {
        if ($this->avatar && file_exists(public_path($this->avatar))) {
            return asset($this->avatar);
        }
        if ($this->avatar && file_exists(public_path('storage/' . $this->avatar))) {
            return asset('storage/' . $this->avatar);
        }
        return null;
    }
}
