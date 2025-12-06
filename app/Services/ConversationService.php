<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\User;
use App\Repositories\ConversationRepository;
use App\Repositories\MessageRepository;
use Illuminate\Database\Eloquent\Collection;

class ConversationService
{
    public function __construct(
        private ConversationRepository $conversationRepository,
        private MessageRepository $messageRepository,
        private NotificationService $notificationService
    ) {}

    /**
     * Get all conversations for a user.
     */
    public function getUserConversations(User $user): Collection
    {
        return $this->conversationRepository->getByUserId($user->id);
    }

    /**
     * Get conversation by ID.
     */
    public function getConversationById(int $id): ?Conversation
    {
        return $this->conversationRepository->findById($id);
    }

    /**
     * Get or create conversation between two users.
     */
    public function getOrCreateConversation(int $userOneId, int $userTwoId, ?int $productId = null): Conversation
    {
        return $this->conversationRepository->findOrCreate($userOneId, $userTwoId, $productId);
    }

    /**
     * Send a message in a conversation.
     */
    public function sendMessage(Conversation $conversation, User $sender, string $content): array
    {
        $message = $this->messageRepository->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'content' => $content,
        ]);

        $this->conversationRepository->updateLastMessageAt($conversation);

        // Load relationships for broadcasting and notifications
        $message->load('sender');
        $conversation->load(['userOne', 'userTwo', 'product']);

        // Get receiver (the other participant)
        $receiver = $conversation->user_one_id === $sender->id 
            ? $conversation->userTwo 
            : $conversation->userOne;

        // Create notification for receiver (only if not the sender)
        if ($receiver && $receiver->id !== $sender->id) {
            $this->notificationService->notifyNewMessage(
                $receiver,
                $conversation->id,
                $conversation->product_id ?? 0,
                $sender->name
            );
        }

        // Broadcast the message event
        broadcast(new MessageSent($message))->toOthers();

        return [
            'message' => $message,
            'conversation' => $conversation,
        ];
    }

    /**
     * Get all messages in a conversation.
     */
    public function getConversationMessages(int $conversationId): Collection
    {
        return $this->messageRepository->getByConversationId($conversationId);
    }

    /**
     * Mark conversation messages as read.
     */
    public function markAsRead(int $conversationId, int $userId): int
    {
        return $this->messageRepository->markConversationAsRead($conversationId, $userId);
    }

    /**
     * Get unread message count for user.
     */
    public function getUnreadCount(User $user): int
    {
        return $this->messageRepository->getUnreadCountForUser($user->id);
    }

    /**
     * Check if user can access conversation.
     */
    public function canAccessConversation(Conversation $conversation, User $user): bool
    {
        return $this->conversationRepository->isParticipant($conversation, $user->id);
    }
}
