<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_id',
        'to_id',
        'body',
        'attachment',
        'seen',
        'delivered',
    ];

    protected $casts = [
        'seen' => 'boolean',
        'delivered' => 'boolean',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'from_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'to_id');
    }

    public function scopeBetween($query, $user1, $user2)
    {
        return $query->where(function ($q) use ($user1, $user2) {
            $q->where('from_id', $user1)->where('to_id', $user2);
        })->orWhere(function ($q) use ($user1, $user2) {
            $q->where('from_id', $user2)->where('to_id', $user1);
        });
    }

    public function scopeUnread($query, $userId)
    {
        return $query->where('to_id', $userId)->where('seen', false);
    }
}
