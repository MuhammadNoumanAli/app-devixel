<?php

namespace App\Models\HR;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendancePunch extends Model
{
    use HasFactory;

    protected $table = 'hr_attendance_punches';

    protected $fillable = [
        'user_id',
        'biometric_thumb_id',
        'punch_time',
        'source',
        'dedup_hash',
        'raw_payload',
    ];

    protected $casts = [
        'punch_time' => 'datetime',
        'raw_payload' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
