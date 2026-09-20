<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $table = 'hr_shifts';

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'grace_minutes',
        'is_night_shift',
        'is_active',
    ];

    protected $casts = [
        'grace_minutes' => 'integer',
        'is_night_shift' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function employees()
    {
        return $this->hasMany(EmployeeProfile::class, 'shift_id');
    }

    public function rosterExceptions()
    {
        return $this->hasMany(RosterException::class, 'shift_id');
    }
}
