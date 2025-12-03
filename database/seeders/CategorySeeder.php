<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Electronics', 'slug' => 'electronics', 'description' => 'Cameras, laptops, gadgets, and tech equipment'],
            ['name' => 'Photography', 'slug' => 'photography', 'description' => 'Cameras, lenses, lighting, and photography equipment'],
            ['name' => 'Sports Equipment', 'slug' => 'sports-equipment', 'description' => 'Bikes, gym equipment, and sports gear'],
            ['name' => 'Tools', 'slug' => 'tools', 'description' => 'Power tools, hand tools, and equipment'],
            ['name' => 'Party Supplies', 'slug' => 'party-supplies', 'description' => 'Party decorations, equipment, and supplies'],
            ['name' => 'Camping Gear', 'slug' => 'camping-gear', 'description' => 'Tents, sleeping bags, and outdoor equipment'],
            ['name' => 'Musical Instruments', 'slug' => 'musical-instruments', 'description' => 'Guitars, keyboards, and audio equipment'],
            ['name' => 'Vehicles', 'slug' => 'vehicles', 'description' => 'Cars, bikes, and transportation'],
        ];

        foreach ($categories as $category) {
            \App\Models\Category::create([
                'name' => $category['name'],
                'slug' => $category['slug'],
                'description' => $category['description'],
                'is_active' => true,
            ]);
        }
    }
}
