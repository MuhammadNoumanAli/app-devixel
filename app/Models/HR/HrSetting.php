<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class HrSetting extends Model
{
    protected $table = 'hr_settings';

    protected $fillable = [
        'grace_period_minutes',
        'late_deduction_percent',
        'qualifying_lead_load_min_amount',
        'qualifying_lead_max_days',
        'sales_agent_lead_bonus_amount',
        'yearly_paid_leaves_quota',
        'max_paid_leaves_per_month',
        'weekend_days',
        'days_in_month_mode',
    ];

    protected $casts = [
        'late_deduction_percent' => 'decimal:2',
        'qualifying_lead_load_min_amount' => 'decimal:2',
        'sales_agent_lead_bonus_amount' => 'decimal:2',
        'weekend_days' => 'array',
    ];

    /**
     * Get the singleton settings instance.
     */
    public static function instance(): self
    {
        return static::first() ?? new static();
    }
}
