<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayslipItem extends Model
{
    use HasFactory;

    protected $table = 'hr_payslip_items';

    protected $fillable = [
        'payslip_id',
        'item_type',
        'code',
        'description',
        'amount',
        'reference_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'reference_date' => 'date',
    ];

    public function payslip()
    {
        return $this->belongsTo(Payslip::class, 'payslip_id');
    }
}
