<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalResource extends JsonResource
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
                'price_per_day' => $this->product->price_per_day,
                'owner' => [
                    'id' => $this->product->user->id,
                    'name' => $this->product->user->name,
                ],
            ],
            'renter' => [
                'id' => $this->renter->id,
                'name' => $this->renter->name,
                'email' => $this->renter->email,
            ],
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date->format('Y-m-d'),
            'total_price' => $this->total_price,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
