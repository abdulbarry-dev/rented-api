<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $authUserId = auth()->id();
        $otherUser = $this->getOtherUser($authUserId);

        return [
            'id' => $this->id,
            'other_user' => [
                'id' => $otherUser->id,
                'name' => $otherUser->name,
                'avatar' => $otherUser->avatar_path,
            ],
            'product' => $this->product ? [
                'id' => $this->product->id,
                'title' => $this->product->title,
                'thumbnail' => $this->product->thumbnail_url,
                'thumbnail_url' => $this->product->thumbnail_url,
                'images' => $this->product->image_urls,
                'image_urls' => $this->product->image_urls,
                'price_per_day' => $this->product->price_per_day,
            ] : null,
            'last_message' => $this->messages->first() ? [
                'content' => $this->messages->first()->content,
                'sender_id' => $this->messages->first()->sender_id,
                'created_at' => $this->messages->first()->created_at->toIso8601String(),
            ] : null,
            'last_message_at' => $this->last_message_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
