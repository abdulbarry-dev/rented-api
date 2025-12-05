<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Electronics & Tech
            ['name' => 'Electronics', 'slug' => 'electronics', 'description' => 'Laptops, computers, tablets, phones, and tech accessories'],
            ['name' => 'Cameras & Photography', 'slug' => 'cameras-photography', 'description' => 'DSLR, mirrorless cameras, lenses, lighting, and photography equipment'],
            ['name' => 'Audio & Music', 'slug' => 'audio-music', 'description' => 'Speakers, headphones, musical instruments, and audio equipment'],
            ['name' => 'Gaming', 'slug' => 'gaming', 'description' => 'Gaming consoles, VR headsets, controllers, and gaming accessories'],
            
            // Vehicles
            ['name' => 'Vehicles', 'slug' => 'vehicles', 'description' => 'Cars, motorcycles, scooters, and vehicle accessories'],
            ['name' => 'Bikes', 'slug' => 'bikes', 'description' => 'Bicycles, mountain bikes, e-bikes, and cycling equipment'],
            
            // Home & Garden
            ['name' => 'Home & Garden', 'slug' => 'home-garden', 'description' => 'Furniture, appliances, decor, and home improvement items'],
            ['name' => 'Tools & Equipment', 'slug' => 'tools-equipment', 'description' => 'Power tools, hand tools, lawn equipment, and machinery'],
            ['name' => 'Appliances', 'slug' => 'appliances', 'description' => 'Kitchen appliances, washing machines, refrigerators, and home electronics'],
            
            // Sports & Outdoors
            ['name' => 'Sports Equipment', 'slug' => 'sports-equipment', 'description' => 'Gym equipment, fitness gear, team sports, and athletic accessories'],
            ['name' => 'Camping & Outdoor', 'slug' => 'camping-outdoor', 'description' => 'Tents, sleeping bags, hiking gear, and outdoor equipment'],
            ['name' => 'Water Sports', 'slug' => 'water-sports', 'description' => 'Kayaks, paddleboards, surfboards, and water equipment'],
            
            // Events & Party
            ['name' => 'Party Supplies', 'slug' => 'party-supplies', 'description' => 'Party equipment, decorations, catering supplies, and event items'],
            ['name' => 'Event Equipment', 'slug' => 'event-equipment', 'description' => 'Tents, chairs, tables, stages, and professional event gear'],
            
            // Fashion & Clothing
            ['name' => 'Clothing & Accessories', 'slug' => 'clothing-accessories', 'description' => 'Designer clothing, costumes, formal wear, and fashion accessories'],
            ['name' => 'Jewelry & Watches', 'slug' => 'jewelry-watches', 'description' => 'Luxury watches, jewelry, and accessories'],
            
            // Baby & Kids
            ['name' => 'Baby & Kids', 'slug' => 'baby-kids', 'description' => 'Strollers, car seats, toys, and children equipment'],
            
            // Entertainment
            ['name' => 'Books & Media', 'slug' => 'books-media', 'description' => 'Textbooks, novels, DVDs, and educational materials'],
            ['name' => 'Games & Toys', 'slug' => 'games-toys', 'description' => 'Board games, collectibles, and recreational toys'],
            
            // Professional
            ['name' => 'Business & Industrial', 'slug' => 'business-industrial', 'description' => 'Commercial equipment, office supplies, and industrial tools'],
            ['name' => 'Medical Equipment', 'slug' => 'medical-equipment', 'description' => 'Mobility aids, medical devices, and healthcare equipment'],
        ];

        foreach ($categories as $category) {
            \App\Models\Category::create([
                'name' => $category['name'],
                'slug' => $category['slug'],
                'description' => $category['description'],
                'is_active' => true,
            ]);
        }

        $this->command->info('✅ Created ' . count($categories) . ' marketplace categories');
    }
}
