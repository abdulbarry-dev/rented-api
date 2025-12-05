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
            'Professional DSLR Camera',
            'Mountain Bike - Trek',
            'Gaming Laptop RTX 4070',
            'Designer Wedding Dress',
            'DeWalt Power Tools Set',
            'Large Party Tent 20x20',
            'Professional DJ Equipment',
            'Complete Camping Gear',
            'Electric Scooter Xiaomi',
            'Sony Home Theater 7.1',
            'Honda Pressure Washer',
            'Burton Snowboard Package',
            'Studio Photography Kit',
            'Rolex Submariner Watch',
            'Louis Vuitton Handbag',
            'PlayStation 5 Bundle',
            'DJI Mavic Pro Drone',
            'Bose Sound System',
            'Professional Lawn Care Set',
            'Event Decorations Package',
        ];

        $cities = ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia', 'San Antonio', 'San Diego', 'Dallas', 'Austin'];
        $states = ['NY', 'CA', 'IL', 'TX', 'AZ', 'PA', 'TX', 'CA', 'TX', 'TX'];
        $conditions = ['new', 'like_new', 'good', 'fair', 'worn'];

        $cityIndex = fake()->numberBetween(0, 9);
        $pricePerDay = fake()->randomFloat(2, 10, 150);

        return [
            'user_id' => \App\Models\User::factory(),
            'category_id' => fake()->numberBetween(1, 8),
            'title' => fake()->randomElement($titles),
            'description' => fake()->paragraph(3),
            'price_per_day' => $pricePerDay,
            'price_per_week' => $pricePerDay * 6, // Discount for weekly
            'price_per_month' => $pricePerDay * 24, // Bigger discount for monthly
            'is_for_sale' => $isForSale,
            'sale_price' => $isForSale ? fake()->randomFloat(2, 200, 3000) : null,
            'is_available' => fake()->boolean(85),
            'thumbnail' => fake()->imageUrl(640, 480, 'products'),
            'images' => [
                fake()->imageUrl(640, 480, 'products'),
                fake()->imageUrl(640, 480, 'products'),
                fake()->imageUrl(640, 480, 'products'),
            ],
            'location_address' => fake()->streetAddress(),
            'location_city' => $cities[$cityIndex],
            'location_state' => $states[$cityIndex],
            'location_country' => 'USA',
            'location_zip' => fake()->postcode(),
            'location_latitude' => fake()->latitude(25, 49),
            'location_longitude' => fake()->longitude(-125, -66),
            'delivery_available' => fake()->boolean(70),
            'delivery_fee' => fake()->randomFloat(2, 5, 50),
            'delivery_radius_km' => fake()->numberBetween(10, 100),
            'pickup_available' => fake()->boolean(90),
            'product_condition' => fake()->randomElement($conditions),
            'security_deposit' => fake()->randomFloat(2, 50, 500),
            'min_rental_days' => fake()->numberBetween(1, 3),
            'max_rental_days' => fake()->numberBetween(7, 60),
        ];
    }
}
