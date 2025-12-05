<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Offer>
 */
class OfferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $offerType = $this->faker->randomElement(['rental', 'purchase']);
        $isRental = $offerType === 'rental';

        return [
            'conversation_id' => \App\Models\Conversation::factory(),
            'product_id' => \App\Models\Product::factory(),
            'sender_id' => \App\Models\User::factory(),
            'receiver_id' => \App\Models\User::factory(),
            'offer_type' => $offerType,
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'start_date' => $isRental ? $this->faker->dateTimeBetween('now', '+30 days') : null,
            'end_date' => $isRental ? $this->faker->dateTimeBetween('+31 days', '+60 days') : null,
            'message' => $this->faker->optional()->sentence(),
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
            'responded_at' => null,
        ];
    }

    public function rental(): static
    {
        return $this->state(fn (array $attributes) => [
            'offer_type' => 'rental',
            'start_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'end_date' => $this->faker->dateTimeBetween('+31 days', '+60 days'),
        ]);
    }

    public function purchase(): static
    {
        return $this->state(fn (array $attributes) => [
            'offer_type' => 'purchase',
            'start_date' => null,
            'end_date' => null,
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'accepted',
            'responded_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'responded_at' => now(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'expires_at' => now()->subDays(1),
        ]);
    }
}
