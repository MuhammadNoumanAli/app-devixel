<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message->load(['sender', 'receiver']);
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('chat.' . $this->message->to_id),
            new Channel('chat.user.' . $this->message->from_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'from_id' => $this->message->from_id,
            'to_id' => $this->message->to_id,
            'body' => $this->message->body,
            'attachment' => $this->message->attachment ? asset('storage/' . $this->message->attachment) : null,
            'seen' => $this->message->seen,
            'created_at' => $this->message->created_at->format('g:i A'),
            'sender' => [
                'id' => $this->message->sender->id,
                'name' => $this->message->sender->full_name,
                'email' => $this->message->sender->email,
            ],
        ];
    }
}
