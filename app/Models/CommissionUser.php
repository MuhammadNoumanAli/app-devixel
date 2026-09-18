<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_type',
        'year_month',
        'active_truck',
        'commission_price',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
