<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ChatController extends Controller
{
    public function index($userId = null)
    {
        $currentUserId = Auth::id();
        Cache::put('user-online-' . $currentUserId, true, now()->addMinutes(2));

        // Only get users with whom the current user has sent or received messages
        $contactIds = Message::where('from_id', $currentUserId)
            ->orWhere('to_id', $currentUserId)
            ->selectRaw('CASE WHEN from_id = ? THEN to_id ELSE from_id END as contact_id', [$currentUserId])
            ->pluck('contact_id')
            ->unique()
            ->filter()
            ->values()
            ->all();

        // If a specific userId was passed in URL (e.g. /chat/3), ensure they are included
        if ($userId && !in_array((int)$userId, $contactIds)) {
            $contactIds[] = (int)$userId;
        }

        $users = User::whereIn('id', $contactIds)
            ->where('id', '!=', $currentUserId)
            ->where('status', 'active')
            ->get()
            ->map(function ($user) use ($currentUserId) {
                $lastMessage = Message::between($currentUserId, $user->id)
                    ->latest()
                    ->first();

                $unreadCount = Message::where('from_id', $user->id)
                    ->where('to_id', $currentUserId)
                    ->where('seen', false)
                    ->count();

                $user->last_message = $lastMessage;
                $user->unread_count = $unreadCount;
                $user->last_activity = $lastMessage ? $lastMessage->created_at : $user->created_at;
                $user->is_online = $user->isOnline();

                return $user;
            })
            ->sortByDesc('last_activity')
            ->values();

        // All active staff for the contacts list & modal
        $allStaff = User::where('id', '!=', $currentUserId)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get()
            ->map(function ($staff) {
                $staff->is_online = $staff->isOnline();
                return $staff;
            });

        // Other contacts: staff with whom there are no messages yet
        $chatUserIds = $users->pluck('id')->all();
        $otherContacts = $allStaff->whereNotIn('id', $chatUserIds)->values();

        // Determine active user
        $activeUser = null;
        $messages = collect();

        if ($userId) {
            $activeUser = $users->firstWhere('id', $userId) ?? $allStaff->firstWhere('id', $userId) ?? User::find($userId);
        } elseif ($users->isNotEmpty()) {
            $activeUser = $users->first();
        } elseif ($otherContacts->isNotEmpty()) {
            $activeUser = $otherContacts->first();
        }

        if ($activeUser) {
            $activeUser->is_online = $activeUser->isOnline();

            // Mark incoming messages as seen AND delivered
            Message::where('from_id', $activeUser->id)
                ->where('to_id', $currentUserId)
                ->where(function ($q) {
                    $q->where('seen', false)->orWhere('delivered', false);
                })
                ->update(['seen' => true, 'delivered' => true]);

            $messages = Message::between($currentUserId, $activeUser->id)
                ->with(['sender', 'receiver'])
                ->orderBy('created_at', 'asc')
                ->get();
        }

        return view('chat.index', compact('users', 'activeUser', 'messages', 'allStaff', 'otherContacts'));
    }

    public function getMessages($userId)
    {
        $currentUserId = Auth::id();
        Cache::put('user-online-' . $currentUserId, true, now()->addMinutes(2));
        $targetUser = User::findOrFail($userId);

        // Mark incoming messages as seen AND delivered
        Message::where('from_id', $userId)
            ->where('to_id', $currentUserId)
            ->where(function ($q) {
                $q->where('seen', false)->orWhere('delivered', false);
            })
            ->update(['seen' => true, 'delivered' => true]);

        $messages = Message::between($currentUserId, $userId)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) use ($currentUserId) {
                return [
                    'id' => $msg->id,
                    'from_id' => $msg->from_id,
                    'to_id' => $msg->to_id,
                    'body' => $msg->body,
                    'attachment' => $msg->attachment ? asset('storage/' . $msg->attachment) : null,
                    'seen' => (bool)$msg->seen,
                    'delivered' => (bool)$msg->delivered,
                    'is_outgoing' => $msg->from_id == $currentUserId,
                    'time' => $msg->created_at->format('g:i A'),
                    'date' => $msg->created_at->format('M d, Y'),
                ];
            });

        return response()->json([
            'status' => 'success',
            'user' => [
                'id' => $targetUser->id,
                'name' => $targetUser->full_name,
                'email' => $targetUser->email,
                'role' => $targetUser->roles->pluck('name')->first() ?? 'User',
                'is_online' => $targetUser->isOnline(),
            ],
            'messages' => $messages,
        ]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'to_id' => 'required|exists:users,id',
            'body' => 'nullable|string',
            'attachment' => 'nullable|file|max:10240',
        ]);

        if (empty($request->body) && !$request->hasFile('attachment')) {
            return response()->json(['status' => 'error', 'message' => 'Message or attachment required'], 422);
        }

        $currentUserId = Auth::id();
        Cache::put('user-online-' . $currentUserId, true, now()->addMinutes(2));

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            if (!Auth::user()->can('chat-send-file') && !Auth::user()->hasRole('Admin')) {
                return response()->json(['status' => 'error', 'message' => 'You do not have permission to send file attachments.'], 403);
            }
            $attachmentPath = $request->file('attachment')->store('chat_attachments', 'public');
        }

        $recipient = User::find($request->to_id);
        $isDelivered = $recipient && $recipient->isOnline();

        $message = Message::create([
            'from_id' => $currentUserId,
            'to_id' => $request->to_id,
            'body' => $request->body,
            'attachment' => $attachmentPath,
            'seen' => false,
            'delivered' => $isDelivered,
        ]);

        try {
            broadcast(new MessageSent($message))->toOthers();
        } catch (\Exception $e) {
            // Broadcasting fallback
        }

        return response()->json([
            'status' => 'success',
            'message' => [
                'id' => $message->id,
                'from_id' => $message->from_id,
                'to_id' => $message->to_id,
                'body' => $message->body,
                'attachment' => $message->attachment ? asset('storage/' . $message->attachment) : null,
                'time' => $message->created_at->format('g:i A'),
                'is_outgoing' => true,
                'seen' => false,
                'delivered' => (bool)$message->delivered,
            ],
        ]);
    }

    public function unreadCount()
    {
        $currentUserId = Auth::id();
        Cache::put('user-online-' . $currentUserId, true, now()->addMinutes(2));

        // Recipient is connected and polling, mark any undelivered messages as delivered
        Message::where('to_id', $currentUserId)
            ->where('delivered', false)
            ->update(['delivered' => true]);

        $unreadMessages = Message::with('sender')
            ->where('to_id', $currentUserId)
            ->where('seen', false)
            ->latest()
            ->get();

        $unreadCount = $unreadMessages->count();

        // Group by sender for dropdown
        $grouped = $unreadMessages->groupBy('from_id')->map(function ($items) {
            $latest = $items->first();
            return [
                'sender_id' => $latest->from_id,
                'sender_name' => $latest->sender->full_name ?? 'User',
                'body' => $latest->body ?? 'Sent an attachment',
                'count' => $items->count(),
                'time' => $latest->created_at->diffForHumans(),
            ];
        })->values();

        return response()->json([
            'count' => $unreadCount,
            'messages' => $grouped,
        ]);
    }

    public function clearChat($userId)
    {
        if (!Auth::user()->can('chat-clear-history') && !Auth::user()->hasRole('Admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to clear chat conversation history.'
            ], 403);
        }

        $currentUserId = Auth::id();
        Message::between($currentUserId, $userId)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Chat history cleared successfully',
        ]);
    }
}
