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

        if ($users->isEmpty() || $categories->isEmpty()) {
            $this->command->warn('⚠️  No users or categories found. Run UserSeeder and CategorySeeder first.');
            return;
        }

        // Use laptop image for all products to test image upload functionality
        $testImage = 'products/images/laptop_test.jpg';

        // Sample products for different categories
        $products = [
            // Electronics
            ['title' => 'MacBook Pro 16" M3 Max', 'description' => 'High-performance laptop for professionals. Perfect for video editing, 3D rendering, and software development.', 'price' => 75.00, 'category' => 'Electronics'],
            ['title' => 'Dell XPS 15 Laptop', 'description' => 'Premium ultrabook with 4K OLED display. Great for designers and content creators.', 'price' => 60.00, 'category' => 'Electronics'],
            ['title' => 'Gaming Laptop RTX 4080', 'description' => 'Ultimate gaming performance with RGB keyboard and 240Hz display.', 'price' => 85.00, 'category' => 'Gaming'],
            ['title' => 'iPad Pro 12.9" with Magic Keyboard', 'description' => 'Professional tablet for digital artists and business professionals.', 'price' => 45.00, 'category' => 'Electronics'],
            
            // Cameras & Photography
            ['title' => 'Canon EOS R5 Camera Body', 'description' => 'Professional mirrorless camera with 8K video. Complete with 2 batteries and memory cards.', 'price' => 120.00, 'category' => 'Cameras & Photography'],
            ['title' => 'Sony A7IV Photography Kit', 'description' => 'Full-frame camera with 24-70mm lens, perfect for weddings and events.', 'price' => 110.00, 'category' => 'Cameras & Photography'],
            ['title' => 'DJI Mavic 3 Pro Drone', 'description' => 'Professional drone with 4/3 CMOS sensor for aerial photography and videography.', 'price' => 95.00, 'category' => 'Cameras & Photography'],
            
            // Vehicles
            ['title' => 'Tesla Model 3 Long Range', 'description' => 'Electric vehicle with autopilot. Perfect for eco-friendly transportation and long trips.', 'price' => 250.00, 'category' => 'Vehicles'],
            ['title' => 'BMW X5 SUV', 'description' => 'Luxury 7-seater SUV ideal for family trips and events. Includes GPS and entertainment system.', 'price' => 180.00, 'category' => 'Vehicles'],
            ['title' => 'Mercedes Sprinter Van', 'description' => 'Spacious van for moving, deliveries, or group transportation. Seats 12 passengers.', 'price' => 150.00, 'category' => 'Vehicles'],
            
            // Bikes
            ['title' => 'Trek Mountain Bike Full Suspension', 'description' => 'Professional trail bike with hydraulic brakes. Perfect for off-road adventures.', 'price' => 35.00, 'category' => 'Bikes'],
            ['title' => 'Rad Power E-Bike', 'description' => 'Electric bike with 50-mile range. Great for commuting and recreation.', 'price' => 40.00, 'category' => 'Bikes'],
            
            // Home & Garden
            ['title' => 'Pressure Washer 3000 PSI', 'description' => 'Professional-grade pressure washer for driveways, decks, and siding.', 'price' => 45.00, 'category' => 'Home & Garden'],
            ['title' => 'Industrial Carpet Cleaner', 'description' => 'Heavy-duty carpet cleaning machine for homes and businesses.', 'price' => 55.00, 'category' => 'Home & Garden'],
            ['title' => 'Lawn Mower Riding Tractor', 'description' => 'Zero-turn riding mower for large properties. Makes lawn care easy and fast.', 'price' => 75.00, 'category' => 'Home & Garden'],
            
            // Tools & Equipment
            ['title' => 'DeWalt 20V Tool Combo Kit', 'description' => 'Complete set: drill, impact driver, circular saw, grinder with 4 batteries.', 'price' => 50.00, 'category' => 'Tools & Equipment'],
            ['title' => 'Table Saw Professional Grade', 'description' => 'Industrial table saw for woodworking projects and construction.', 'price' => 65.00, 'category' => 'Tools & Equipment'],
            ['title' => 'Scaffolding Set 20ft', 'description' => 'Complete scaffolding system for construction and painting projects.', 'price' => 80.00, 'category' => 'Tools & Equipment'],
            
            // Sports & Fitness
            ['title' => 'Peloton Bike+', 'description' => 'Smart exercise bike with rotating screen and premium membership included.', 'price' => 55.00, 'category' => 'Sports Equipment'],
            ['title' => 'Home Gym Power Rack', 'description' => 'Complete weight training station with barbell, plates, and bench.', 'price' => 70.00, 'category' => 'Sports Equipment'],
            ['title' => 'Treadmill Commercial Grade', 'description' => 'Professional treadmill with incline and workout programs.', 'price' => 60.00, 'category' => 'Sports Equipment'],
            
            // Camping & Outdoor
            ['title' => '6-Person Camping Tent', 'description' => 'Waterproof family tent with separate rooms and vestibule.', 'price' => 30.00, 'category' => 'Camping & Outdoor'],
            ['title' => 'Kayak Inflatable 2-Person', 'description' => 'Portable kayak with paddles and pump. Perfect for lakes and calm rivers.', 'price' => 40.00, 'category' => 'Water Sports'],
            ['title' => 'Paddleboard with Accessories', 'description' => 'Inflatable SUP board with paddle, pump, and carry bag.', 'price' => 35.00, 'category' => 'Water Sports'],
            
            // Party & Events
            ['title' => 'Party Tent 20x20ft', 'description' => 'Large event tent for weddings, birthdays, and outdoor gatherings. Seats 40 guests.', 'price' => 150.00, 'category' => 'Event Equipment'],
            ['title' => 'Sound System DJ Setup', 'description' => 'Professional PA system with speakers, mixer, and microphones for events.', 'price' => 120.00, 'category' => 'Audio & Music'],
            ['title' => 'Projector & 120" Screen', 'description' => '4K projector with large screen for movie nights and presentations.', 'price' => 65.00, 'category' => 'Event Equipment'],
            ['title' => 'Folding Tables & Chairs (50 Set)', 'description' => 'Commercial-grade tables and chairs for events and parties.', 'price' => 100.00, 'category' => 'Event Equipment'],
            
            // Audio & Music
            ['title' => 'Electric Guitar & Amp Package', 'description' => 'Fender Stratocaster with Marshall amp. Perfect for gigs and practice.', 'price' => 45.00, 'category' => 'Audio & Music'],
            ['title' => 'Roland Digital Piano 88-Key', 'description' => 'Weighted keys digital piano for performances and learning.', 'price' => 55.00, 'category' => 'Audio & Music'],
            ['title' => 'DJ Controller with Lighting', 'description' => 'Complete DJ setup with controller, laptop stand, and LED lights.', 'price' => 85.00, 'category' => 'Audio & Music'],
            
            // Gaming
            ['title' => 'PlayStation 5 Bundle', 'description' => 'PS5 console with 2 controllers, VR headset, and 10 popular games.', 'price' => 40.00, 'category' => 'Gaming'],
            ['title' => 'Gaming PC RTX 4090', 'description' => 'High-end gaming desktop with RGB setup. Runs any game at max settings.', 'price' => 95.00, 'category' => 'Gaming'],
            ['title' => 'VR Racing Simulator Rig', 'description' => 'Complete racing setup with wheel, pedals, shifter, and VR headset.', 'price' => 75.00, 'category' => 'Gaming'],
        ];

        foreach ($products as $productData) {
            $category = $categories->firstWhere('name', $productData['category']);
            
            if (!$category) {
                $this->command->warn("⚠️  Category '{$productData['category']}' not found for {$productData['title']}");
                continue;
            }

            \App\Models\Product::create([
                'user_id' => $users->random()->id,
                'category_id' => $category->id,
                'title' => $productData['title'],
                'description' => $productData['description'],
                'price_per_day' => $productData['price'],
                'is_for_sale' => rand(0, 1) == 1, // 50% chance of being for sale
                'sale_price' => rand(0, 1) == 1 ? $productData['price'] * rand(20, 30) : null,
                'is_available' => true,
                'verification_status' => 'approved', // Make products visible in API
                'thumbnail' => null, // No thumbnail, just images
                'images' => [$testImage, $testImage, $testImage], // Use laptop image 3 times
            ]);
        }

        $this->command->info('✅ Created ' . count($products) . ' test products with laptop images');
    }
}
