<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing users and categories
        $users = \App\Models\User::all();
        $categories = \App\Models\Category::all();

        $thumbnails = [
            'products/thumbnails/product_1.webp',
            'products/thumbnails/product_2.jpeg',
            'products/thumbnails/product_3.png',
        ];

        $images = [
            'products/images/product_1_1.webp',
            'products/images/product_2_1.jpeg',
            'products/images/product_3_1.png',
        ];

        // Create 50 sample products with images
        foreach (range(1, 50) as $index) {
            $thumbnailIndex = ($index - 1) % count($thumbnails);
            $imageIndex = ($index - 1) % count($images);

            \App\Models\Product::factory()->create([
                'user_id' => $users->random()->id,
                'category_id' => $categories->random()->id,
                'thumbnail' => $thumbnails[$thumbnailIndex],
                'images' => json_encode([
                    $images[$imageIndex],
                ]),
            ]);
        }
    }
}
