<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'product' => [
                'id' => $this->product->id,
                'title' => $this->product->title,
                'thumbnail_url' => $this->product->thumbnail_url,
                'price_per_day' => $this->product->price_per_day,
            ],
            'sender' => [
                'id' => $this->sender->id,
                'name' => $this->sender->name,
                'avatar_url' => $this->sender->avatar_url ?? null,
            ],
            'receiver' => [
                'id' => $this->receiver->id,
                'name' => $this->receiver->name,
                'avatar_url' => $this->receiver->avatar_url ?? null,
            ],
            'offer_type' => $this->offer_type,
            'amount' => (float) $this->amount,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'message' => $this->message,
            'status' => $this->status,
            'is_pending' => $this->isPending(),
            'is_accepted' => $this->isAccepted(),
            'is_rejected' => $this->isRejected(),
            'is_expired' => $this->isExpired(),
            'can_be_responded' => $this->canBeResponded(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'responded_at' => $this->responded_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
