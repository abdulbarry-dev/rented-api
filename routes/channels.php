<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

// User private channels for notifications
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Conversation presence channels
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);
    
    if (! $conversation) {
        return false;
    }
    
    // Check if user is a participant in the conversation
    return $conversation->user_one_id === $user->id || $conversation->user_two_id === $user->id;
}, ['guarded' => false]);
