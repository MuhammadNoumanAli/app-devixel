<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mc_number',
        'owner_name',
        'load_number',
        'pick_location',
        'delivery_location',
        'load_date',
        'pick_date',
        'delivery_date',
        'driver_name',
        'truck_number',
        'trailer_number',
        'driver_number',
        'total_miles',
        'rate',
        'percentage',
        'receivable',
        'broker_company_name',
        'broker_mc',
        'broker_number',
        'broker_email',
        'broker_rep_name',
        'rate_confirmation',
        'bol_pod',
        'additional_doc',
        'comment',
        'notification_status',
        'invoice_generate',
        'invoice_status',
        'is_cancel',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class, 'mc_number', 'mc_number');
    }

    public function invoiceDispatches()
    {
        return $this->hasMany(InvoiceDispatch::class);
    }
}
