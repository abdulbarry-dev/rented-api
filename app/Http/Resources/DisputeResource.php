<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DisputeResource extends JsonResource
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
            'rental_id' => $this->rental_id,
            'purchase_id' => $this->purchase_id,
            'dispute_type' => $this->dispute_type,
            'status' => $this->status,
            'description' => $this->description,
            'evidence' => $this->evidence,
            'resolution' => $this->resolution,
            'reporter' => [
                'id' => $this->reporter->id,
                'name' => $this->reporter->name,
            ],
            'reported_user' => [
                'id' => $this->reportedUser->id,
                'name' => $this->reportedUser->name,
            ],
            'created_at' => $this->created_at->toIso8601String(),
            'resolved_at' => $this->resolved_at?->toIso8601String(),
        ];
    }
}
