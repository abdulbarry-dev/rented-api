<?php

namespace App\Events;

use App\Models\Rental;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RentalStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Rental $rental,
        public string $oldStatus
    ) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.'.$this->rental->product->user_id),
            new PrivateChannel('user.'.$this->rental->renter_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'rental.status.changed';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->rental->id,
            'product_id' => $this->rental->product_id,
            'renter_id' => $this->rental->renter_id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->rental->status,
            'start_date' => $this->rental->start_date->toDateString(),
            'end_date' => $this->rental->end_date->toDateString(),
            'total_price' => $this->rental->total_price,
            'updated_at' => $this->rental->updated_at->toIso8601String(),
            'product' => [
                'id' => $this->rental->product->id,
                'title' => $this->rental->product->title,
            ],
        ];
    }
}

