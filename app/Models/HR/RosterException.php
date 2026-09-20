<?php

namespace App\Models\HR;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RosterException extends Model
{
    use HasFactory;

    protected $table = 'hr_roster_exceptions';

    protected $fillable = [
        'exception_date',
        'type',
        'shift_id',
        'user_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'exception_date' => 'date',
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
