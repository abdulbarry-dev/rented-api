<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseResource extends JsonResource
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
            'product' => [
                'id' => $this->product->id,
                'title' => $this->product->title,
                'thumbnail' => $this->product->thumbnail,
                'sale_price' => $this->product->sale_price,
                'owner' => [
                    'id' => $this->product->user->id,
                    'name' => $this->product->user->name,
                ],
            ],
            'buyer' => [
                'id' => $this->buyer->id,
                'name' => $this->buyer->name,
                'email' => $this->buyer->email,
            ],
            'purchase_price' => $this->purchase_price,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
