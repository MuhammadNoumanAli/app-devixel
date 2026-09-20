<?php

namespace App\Models\HR;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeProfile extends Model
{
    use HasFactory;

    protected $table = 'hr_employee_profiles';

    protected $fillable = [
        'user_id',
        'employee_code',
        'shift_id',
        'designation',
        'department',
        'joining_date',
        'base_salary',
        'biometric_thumb_id',
        'bank_name',
        'bank_account_number',
        'iban',
        'emergency_contact_name',
        'emergency_contact_phone',
        'monthly_load_target_amount',
        'target_bonus_type',
        'target_bonus_fixed',
        'target_bonus_percentage',
        'status',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'base_salary' => 'decimal:2',
        'monthly_load_target_amount' => 'decimal:2',
        'target_bonus_fixed' => 'decimal:2',
        'target_bonus_percentage' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function getDailySalaryRateAttribute()
    {
        return round(($this->base_salary ?? 0) / 30, 2);
    }
}
