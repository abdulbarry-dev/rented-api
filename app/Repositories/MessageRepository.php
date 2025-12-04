<?php

namespace App\Repositories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;

class MessageRepository
{
    /**
     * Get all messages for a conversation.
     */
    public function getByConversationId(int $conversationId): Collection
    {
        return Message::with('sender')
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Create a new message.
     */
    public function create(array $data): Message
    {
        return Message::create($data);
    }

    /**
     * Mark message as read.
     */
    public function markAsRead(Message $message): bool
    {
        return $message->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Mark all messages in a conversation as read for a user.
     */
    public function markConversationAsRead(int $conversationId, int $userId): int
    {
        return Message::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Get unread message count for a user.
     */
    public function getUnreadCountForUser(int $userId): int
    {
        return Message::whereHas('conversation', function ($query) use ($userId) {
            $query->where('user_one_id', $userId)
                ->orWhere('user_two_id', $userId);
        })
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->count();
    }
}
