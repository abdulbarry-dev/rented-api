<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isForSale = fake()->boolean(30); // 30% chance of being for sale

        $titles = [
            'Professional Camera Equipment',
            'Mountain Bike',
            'Gaming Laptop',
            'Wedding Dress',
            'Power Tools Set',
            'Party Tent',
            'DJ Equipment',
            'Camping Gear Bundle',
            'Electric Scooter',
            'Home Theater System',
            'Pressure Washer',
            'Snowboard Equipment',
            'Studio Photography Lights',
            'Luxury Watch',
            'Designer Handbag',
            'Gaming Console',
            'Drone with Camera',
            'Sound System',
            'Lawn Equipment',
            'Party Decorations Set',
        ];

        return [
            'user_id' => \App\Models\User::factory(),
            'category_id' => fake()->numberBetween(1, 8), // Assuming categories 1-8 exist
            'title' => fake()->randomElement($titles),
            'description' => fake()->paragraph(3),
            'price_per_day' => fake()->randomFloat(2, 10, 150),
            'is_for_sale' => $isForSale,
            'sale_price' => $isForSale ? fake()->randomFloat(2, 200, 3000) : null,
            'is_available' => fake()->boolean(85), // 85% available
            'thumbnail' => fake()->imageUrl(640, 480, 'products'),
            'images' => [
                fake()->imageUrl(640, 480, 'products'),
                fake()->imageUrl(640, 480, 'products'),
                fake()->imageUrl(640, 480, 'products'),
            ],
        ];
    }
}
