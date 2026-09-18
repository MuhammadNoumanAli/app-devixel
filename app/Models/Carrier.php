<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrier extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'assign_to',
        'mc_number',
        'dot',
        'name',
        'email',
        'number',
        'company_name',
        'truck_type',
        'truck_size',
        'maximum_weight',
        'payment_type',
        'percent_flat',
        'charge_type',
        'mc_letter',
        'w_form',
        'coi',
        'noa',
        'void_cheque',
        'extra_document',
        'all_zones',
        'z0', 'z1', 'z2', 'z3', 'z4', 'z5', 'z6', 'z7', 'z8', 'z9',
        'street_address',
        'city_name',
        'state_name',
        'zip_code',
        'rpm',
        'comment',
        'notification_status',
        'active_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assign_to', 'id');
    }

    public function truckSize()
    {
        return $this->belongsTo(TruckSize::class, 'truck_size');
    }

    public function truckType()
    {
        return $this->belongsTo(TruckType::class, 'truck_type');
    }

    public function paymentType()
    {
        return $this->belongsTo(PaymentType::class, 'payment_type');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_name');
    }

    public function dispatches()
    {
        return $this->hasMany(Dispatch::class, 'mc_number', 'mc_number');
    }
}
