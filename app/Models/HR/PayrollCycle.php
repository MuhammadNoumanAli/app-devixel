<?php

namespace App\Models\HR;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollCycle extends Model
{
    use HasFactory;

    protected $table = 'hr_payroll_cycles';

    protected $fillable = [
        'cycle_code',
        'start_date',
        'end_date',
        'processed_by',
        'status',
        'total_gross',
        'total_net',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_gross' => 'decimal:2',
        'total_net' => 'decimal:2',
    ];

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function payslips()
    {
        return $this->hasMany(Payslip::class, 'payroll_cycle_id');
    }

    public function isLocked()
    {
        return $this->status === 'locked';
    }
}
