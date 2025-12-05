<?php

namespace App\Events;

use App\Models\Rental;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RentalCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Rental $rental
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
        return 'rental.created';
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
            'status' => $this->rental->status,
            'start_date' => $this->rental->start_date->toDateString(),
            'end_date' => $this->rental->end_date->toDateString(),
            'total_price' => $this->rental->total_price,
            'created_at' => $this->rental->created_at->toIso8601String(),
            'product' => [
                'id' => $this->rental->product->id,
                'title' => $this->rental->product->title,
                'thumbnail' => $this->rental->product->thumbnail,
            ],
        ];
    }
}

