<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarrierNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'carrier_id',
        'user_id',
        'status',
        'message',
        'attachment',
        'attachment_original_name',
    ];

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
