<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\MessageResource;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;

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
}
