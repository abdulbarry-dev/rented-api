<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserVerification>
 */
class UserVerificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'verification_images' => [
                'id_front' => 'verifications/national-ids/'.fake()->uuid().'.jpg',
                'id_back' => 'verifications/national-ids/'.fake()->uuid().'.jpg',
                'selfie' => 'verifications/selfies/'.fake()->uuid().'.jpg',
            ],
            'status' => 'pending',
            'admin_notes' => null,
            'submitted_at' => now(),
            'reviewed_at' => null,
        ];
    }

    /**
     * Indicate that the verification is verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'verified',
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Indicate that the verification is rejected.
     */
    public function rejected(string $reason = 'Documents unclear'): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'admin_notes' => $reason,
            'reviewed_at' => now(),
        ]);
    }
}
