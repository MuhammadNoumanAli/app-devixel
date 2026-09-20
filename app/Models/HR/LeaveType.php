<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;

    protected $table = 'hr_leave_types';

    protected $fillable = [
        'name',
        'is_paid',
        'default_quota',
        'is_active',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'default_quota' => 'integer',
        'is_active' => 'boolean',
    ];

    public function leaves()
    {
        return $this->hasMany(Leave::class, 'leave_type_id');
    }

    public function userQuotas()
    {
        return $this->hasMany(UserLeaveQuota::class, 'leave_type_id');
    }
}
