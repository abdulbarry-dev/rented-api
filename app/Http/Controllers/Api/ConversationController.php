<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ConversationController extends Controller
{
    public function __construct(
        private ConversationService $service
    ) {}

    /**
     * Get all conversations for the authenticated user.
     */
    public function index(): AnonymousResourceCollection
    {
        $conversations = $this->service->getUserConversations(auth()->user());

        return ConversationResource::collection($conversations);
    }

    /**
     * Get a specific conversation with messages.
     */
    public function show(int $id): ConversationResource|JsonResponse
    {
        $conversation = $this->service->getConversationById($id);

        if (! $conversation) {
            return response()->json([
                'message' => 'Conversation not found.',
            ], 404);
        }

        if (! $this->service->canAccessConversation($conversation, auth()->user())) {
            return response()->json([
                'message' => 'You do not have access to this conversation.',
            ], 403);
        }

        return new ConversationResource($conversation);
    }

    /**
     * Get all messages in a conversation.
     */
    public function messages(int $id): AnonymousResourceCollection|JsonResponse
    {
        $conversation = $this->service->getConversationById($id);

        if (! $conversation) {
            return response()->json([
                'message' => 'Conversation not found.',
            ], 404);
        }

        if (! $this->service->canAccessConversation($conversation, auth()->user())) {
            return response()->json([
                'message' => 'You do not have access to this conversation.',
            ], 403);
        }

        $messages = $this->service->getConversationMessages($id);

        return MessageResource::collection($messages);
    }

    /**
     * Mark conversation messages as read.
     */
    public function markAsRead(int $id): JsonResponse
    {
        $conversation = $this->service->getConversationById($id);

        if (! $conversation) {
            return response()->json([
                'message' => 'Conversation not found.',
            ], 404);
        }

        if (! $this->service->canAccessConversation($conversation, auth()->user())) {
            return response()->json([
                'message' => 'You do not have access to this conversation.',
            ], 403);
        }

        $count = $this->service->markAsRead($id, auth()->id());

        return response()->json([
            'message' => 'Messages marked as read.',
            'count' => $count,
        ]);
    }

    /**
     * Get unread message count.
     */
    public function unreadCount(): JsonResponse
    {
        $count = $this->service->getUnreadCount(auth()->user());

        return response()->json([
            'unread_count' => $count,
        ]);
    }
}
