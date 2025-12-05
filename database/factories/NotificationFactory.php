<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = [
            'product_approved',
            'product_rejected',
            'rental_requested',
            'rental_confirmed',
            'rental_completed',
            'purchase_ordered',
            'purchase_completed',
            'new_message',
            'offer_received',
            'offer_accepted',
            'offer_rejected',
            'review_received',
            'dispute_opened',
            'dispute_resolved',
        ];

        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement($types),
            'title' => fake()->sentence(3),
            'message' => fake()->sentence(10),
            'data' => [
                'product_id' => fake()->numberBetween(1, 50),
                'amount' => fake()->randomFloat(2, 10, 500),
            ],
            'read_at' => fake()->optional(0.5)->dateTimeBetween('-1 week', 'now'),
        ];
    }

    /**
     * Indicate that the notification is unread.
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => null,
        ]);
    }

    /**
     * Indicate that the notification is read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ]);
    }
}
