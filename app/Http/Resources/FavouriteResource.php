<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavouriteResource extends JsonResource
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
                'description' => $this->product->description,
                'price_per_day' => $this->product->price_per_day,
                'is_for_sale' => $this->product->is_for_sale,
                'sale_price' => $this->product->sale_price,
                'thumbnail' => $this->product->thumbnail_url,
                'category' => $this->product->category->name ?? null,
                'owner' => [
                    'id' => $this->product->user->id,
                    'name' => $this->product->user->name,
                ],
            ],
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
