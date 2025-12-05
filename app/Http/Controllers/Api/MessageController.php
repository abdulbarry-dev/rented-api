<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageRead;
use App\Events\TypingIndicator;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Message;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(
        private ConversationService $service
    ) {}

    /**
     * Send a new message.
     */
    public function store(StoreMessageRequest $request): MessageResource|JsonResponse
    {
        $validated = $request->validated();
        $user = auth()->user();

        // Get or create conversation
        if (isset($validated['conversation_id'])) {
            $conversation = $this->service->getConversationById($validated['conversation_id']);

            if (! $conversation) {
                return response()->json([
                    'message' => 'Conversation not found.',
                ], 404);
            }

            if (! $this->service->canAccessConversation($conversation, $user)) {
                return response()->json([
                    'message' => 'You do not have access to this conversation.',
                ], 403);
            }
        } else {
            // Create new conversation
            $conversation = $this->service->getOrCreateConversation(
                $user->id,
                $validated['receiver_id'],
                $validated['product_id'] ?? null
            );
        }

        // Send message
        $result = $this->service->sendMessage($conversation, $user, $validated['content']);

        return new MessageResource($result['message']);
    }

    /**
     * Mark messages as read.
     */
    public function markAsRead(Request $request, int $conversationId): JsonResponse
    {
        $user = auth()->user();
        $conversation = $this->service->getConversationById($conversationId);

        if (! $conversation) {
            return response()->json([
                'message' => 'Conversation not found.',
            ], 404);
        }

        if (! $this->service->canAccessConversation($conversation, $user)) {
            return response()->json([
                'message' => 'You do not have access to this conversation.',
            ], 403);
        }

        $count = $this->service->markAsRead($conversationId, $user->id);

        // Broadcast read event for all messages in conversation
        $messages = $this->service->getConversationMessages($conversationId);
        foreach ($messages as $message) {
            if ($message->sender_id !== $user->id && ! $message->is_read) {
                $message->update(['is_read' => true, 'read_at' => now()]);
                broadcast(new MessageRead($message));
            }
        }

        return response()->json([
            'message' => 'Messages marked as read.',
            'count' => $count,
        ]);
    }

    /**
     * Send typing indicator.
     */
    public function typing(Request $request, int $conversationId): JsonResponse
    {
        $user = auth()->user();
        $conversation = $this->service->getConversationById($conversationId);

        if (! $conversation) {
            return response()->json([
                'message' => 'Conversation not found.',
            ], 404);
        }

        if (! $this->service->canAccessConversation($conversation, $user)) {
            return response()->json([
                'message' => 'You do not have access to this conversation.',
            ], 403);
        }

        $isTyping = $request->boolean('is_typing', true);
        broadcast(new TypingIndicator($conversationId, $user, $isTyping))->toOthers();

        return response()->json([
            'message' => 'Typing indicator sent.',
        ]);
    }
}
