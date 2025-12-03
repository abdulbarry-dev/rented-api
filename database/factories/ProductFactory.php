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

        return [
            'user_id' => \App\Models\User::factory(),
            'category_id' => \App\Models\Category::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'price_per_day' => fake()->randomFloat(2, 5, 200),
            'is_for_sale' => $isForSale,
            'sale_price' => $isForSale ? fake()->randomFloat(2, 100, 5000) : null,
            'is_available' => fake()->boolean(85), // 85% available
            'thumbnail' => fake()->imageUrl(640, 480, 'products'),
            'images' => [
                fake()->imageUrl(640, 480, 'products'),
                fake()->imageUrl(640, 480, 'products'),
            ],
        ];
    }
}
