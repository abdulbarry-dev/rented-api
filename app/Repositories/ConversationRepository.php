<?php

namespace App\Repositories;

use App\Models\Conversation;
use Illuminate\Database\Eloquent\Collection;

class ConversationRepository
{
    /**
     * Get all conversations for a user.
     */
    public function getByUserId(int $userId): Collection
    {
        return Conversation::with(['userOne', 'userTwo', 'product', 'messages' => function ($query) {
            $query->latest()->limit(1);
        }])
            ->where(function ($query) use ($userId) {
                $query->where('user_one_id', $userId)
                    ->orWhere('user_two_id', $userId);
            })
            ->orderBy('last_message_at', 'desc')
            ->get();
    }

    /**
     * Find conversation by ID.
     */
    public function findById(int $id): ?Conversation
    {
        return Conversation::with(['userOne', 'userTwo', 'product'])->find($id);
    }

    /**
     * Find or create conversation between two users.
     */
    public function findOrCreate(int $userOneId, int $userTwoId, ?int $productId = null): Conversation
    {
        // Ensure consistent ordering
        $lowerUserId = min($userOneId, $userTwoId);
        $higherUserId = max($userOneId, $userTwoId);

        return Conversation::firstOrCreate(
            [
                'user_one_id' => $lowerUserId,
                'user_two_id' => $higherUserId,
            ],
            [
                'product_id' => $productId,
            ]
        );
    }

    /**
     * Update conversation's last message timestamp.
     */
    public function updateLastMessageAt(Conversation $conversation): bool
    {
        return $conversation->update(['last_message_at' => now()]);
    }

    /**
     * Check if user is participant in conversation.
     */
    public function isParticipant(Conversation $conversation, int $userId): bool
    {
        return $conversation->user_one_id === $userId || $conversation->user_two_id === $userId;
    }
}
