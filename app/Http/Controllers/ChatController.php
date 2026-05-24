<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Get chat messages between current user and the other party.
     */
    public function getMessages(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($user->isAdmin()) {
            // Admin needs a customer_id to load the chat
            $customerId = $request->query('customer_id');
            if (!$customerId) {
                return response()->json(['error' => 'Customer ID is required'], 400);
            }
            $otherUserId = $customerId;
        } else {
            // Customer chats with the first admin found
            $admin = User::where('role', 'admin')->first();
            if (!$admin) {
                return response()->json(['error' => 'No admin available at the moment.'], 404);
            }
            $otherUserId = $admin->id;
        }

        // Fetch messages
        $messages = Chat::where(function ($query) use ($user, $otherUserId) {
            $query->where('sender_id', $user->id)
                  ->where('receiver_id', $otherUserId);
        })->orWhere(function ($query) use ($user, $otherUserId) {
            $query->where('sender_id', $otherUserId)
                  ->where('receiver_id', $user->id);
        })
        ->orderBy('created_at', 'asc')
        ->get()
        ->map(function ($message) use ($user) {
            return [
                'id' => $message->id,
                'message' => $message->message,
                'is_sender' => $message->sender_id === $user->id,
                'time' => $message->created_at->format('H:i'),
                'date' => $message->created_at->format('d M Y'),
                'is_read' => $message->is_read,
            ];
        });

        // Mark incoming messages as read
        Chat::where('sender_id', $otherUserId)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $otherUser = User::find($otherUserId);
        $isOnline = $otherUser && $otherUser->last_seen_at && $otherUser->last_seen_at->diffInMinutes(now()) < 2;

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'other_user' => $otherUser,
            'other_user_online' => $isOnline,
        ]);
    }

    /**
     * Send a new message.
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($user->isAdmin()) {
            $receiverId = $request->input('receiver_id');
            if (!$receiverId) {
                return response()->json(['error' => 'Receiver (Customer) ID is required'], 400);
            }
        } else {
            // Customer sends to admin
            $admin = User::where('role', 'admin')->first();
            if (!$admin) {
                return response()->json(['error' => 'No admin available at the moment.'], 404);
            }
            $receiverId = $admin->id;
        }

        $chat = Chat::create([
            'sender_id' => $user->id,
            'receiver_id' => $receiverId,
            'message' => $request->message,
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'chat' => [
                'id' => $chat->id,
                'message' => $chat->message,
                'is_sender' => true,
                'time' => $chat->created_at->format('H:i'),
                'date' => $chat->created_at->format('d M Y'),
                'is_read' => false,
            ]
        ]);
    }

    /**
     * Get active conversations list (For Admin only).
     */
    public function getConversations(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            return response()->json(['error' => 'Unauthorized or not an admin'], 401);
        }

        $adminId = $user->id;
        
        // Search filter
        $search = $request->query('search');

        $customersQuery = User::where('role', 'customer');
        if ($search) {
            $customersQuery->where('name', 'like', '%' . $search . '%');
        }

        $customers = $customersQuery->get()
            ->map(function ($customer) use ($adminId) {
                // Get latest message between this customer and admin
                $latestMessage = Chat::where(function ($q) use ($customer, $adminId) {
                    $q->where('sender_id', $customer->id)->where('receiver_id', $adminId);
                })->orWhere(function ($q) use ($customer, $adminId) {
                    $q->where('sender_id', $adminId)->where('receiver_id', $customer->id);
                })->latest()->first();

                // Get unread count
                $unreadCount = Chat::where('sender_id', $customer->id)
                    ->where('receiver_id', $adminId)
                    ->where('is_read', false)
                    ->count();

                $isOnline = $customer->last_seen_at && $customer->last_seen_at->diffInMinutes(now()) < 2;

                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'initials' => strtoupper(substr($customer->name, 0, 2)),
                    'latest_message' => $latestMessage ? $latestMessage->message : 'Belum ada obrolan',
                    'latest_message_time' => $latestMessage ? $latestMessage->created_at->format('H:i') : '',
                    'latest_message_timestamp' => $latestMessage ? $latestMessage->created_at->timestamp : 0,
                    'unread_count' => $unreadCount,
                    'is_online' => $isOnline,
                ];
            })
            ->sortByDesc('latest_message_timestamp')
            ->values();

        // Calculate total unread messages for admin dashboard badge
        $totalUnread = Chat::where('receiver_id', $adminId)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'conversations' => $customers,
            'total_unread' => $totalUnread
        ]);
    }

    /**
     * Get global unread badge count for currently authenticated user.
     */
    public function getUnreadCount()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['unread_count' => 0]);
        }

        $count = Chat::where('receiver_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $count
        ]);
    }

    /**
     * Heartbeat endpoint to track user online status.
     */
    public function heartbeat()
    {
        $user = Auth::user();
        if ($user) {
            $user->last_seen_at = now();
            $user->save();
        }

        return response()->json(['success' => true]);
    }
}
