<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TruckType extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function carriers()
    {
        return $this->hasMany(Carrier::class, 'truck_type');
    }
}
