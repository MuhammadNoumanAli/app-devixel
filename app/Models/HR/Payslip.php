<?php

namespace App\Models\HR;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    use HasFactory;

    protected $table = 'hr_payslips';

    protected $fillable = [
        'payroll_cycle_id',
        'user_id',
        'eligible_work_days',
        'present_days',
        'paid_leave_days',
        'unpaid_leave_days',
        'late_count',
        'base_salary_earned',
        'sales_lead_bonus_earned',
        'dispatcher_target_bonus_earned',
        'dispatcher_load_commission_earned',
        'total_earnings',
        'late_deductions_total',
        'unpaid_leave_deductions_total',
        'loan_deductions_total',
        'total_deductions',
        'net_salary',
        'payment_status',
        'payment_method',
    ];

    protected $casts = [
        'eligible_work_days' => 'integer',
        'present_days' => 'decimal:1',
        'paid_leave_days' => 'decimal:1',
        'unpaid_leave_days' => 'decimal:1',
        'late_count' => 'integer',
        'base_salary_earned' => 'decimal:2',
        'sales_lead_bonus_earned' => 'decimal:2',
        'dispatcher_target_bonus_earned' => 'decimal:2',
        'dispatcher_load_commission_earned' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'late_deductions_total' => 'decimal:2',
        'unpaid_leave_deductions_total' => 'decimal:2',
        'loan_deductions_total' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    public function cycle()
    {
        return $this->belongsTo(PayrollCycle::class, 'payroll_cycle_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items()
    {
        return $this->hasMany(PayslipItem::class, 'payslip_id');
    }

    public function earningItems()
    {
        return $this->items()->where('item_type', 'earning');
    }

    public function deductionItems()
    {
        return $this->items()->where('item_type', 'deduction');
    }
}
