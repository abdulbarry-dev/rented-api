<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'price_per_day' => (float) $this->price_per_day,
            'is_for_sale' => $this->is_for_sale,
            'sale_price' => $this->sale_price ? (float) $this->sale_price : null,
            'is_available' => $this->is_available,
            'thumbnail' => $this->thumbnail,
            'images' => $this->images ?? [],
            'category' => new CategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
