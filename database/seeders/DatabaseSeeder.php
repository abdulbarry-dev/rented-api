<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Only seed if database is empty
        if (User::count() > 0) {
            $this->command->info('Database already seeded. Skipping...');

            return;
        }

        $this->command->info('🌱 Seeding database with realistic data...');

        // Copy images from public storage to proper directories
        $this->setupStorageDirectories();

        // Create test users with avatars
        $this->command->info('👤 Creating users...');
        $this->call(UserSeeder::class);
        $users = User::all();

        // Seed categories and products
        $this->command->info('📦 Creating categories and products...');
        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
        ]);

        // Create reviews
        $this->command->info('⭐ Creating reviews...');
        $this->createReviews($users);

        // Create favourites
        $this->command->info('❤️ Creating favourites...');
        $this->createFavourites($users);

        // Create conversations and messages
        $this->command->info('💬 Creating conversations...');
        $this->createConversations($users);

        $this->command->info('✅ Database seeding completed successfully!');
    }

    private function setupStorageDirectories(): void
    {
        $directories = [
            'avatars',
            'products/thumbnails',
            'products/images',
            'verifications',
        ];

        foreach ($directories as $dir) {
            Storage::disk('public')->makeDirectory($dir);
        }

        // Copy sample images to different directories
        $sourceImages = ['1.webp', '2.jpeg', '3.png'];

        foreach ($sourceImages as $index => $image) {
            $sourcePath = storage_path('app/public/'.$image);

            if (File::exists($sourcePath)) {
                // Copy to avatars
                File::copy($sourcePath, storage_path('app/public/avatars/user_'.($index + 1).'.'.pathinfo($image, PATHINFO_EXTENSION)));

                // Copy to product thumbnails
                File::copy($sourcePath, storage_path('app/public/products/thumbnails/product_'.($index + 1).'.'.pathinfo($image, PATHINFO_EXTENSION)));

                // Copy to product images
                File::copy($sourcePath, storage_path('app/public/products/images/product_'.($index + 1).'_1.'.pathinfo($image, PATHINFO_EXTENSION)));
            }
        }
    }

    private function createReviews($users): void
    {
        $products = \App\Models\Product::all();

        // Create 30 reviews
        foreach (range(1, 30) as $i) {
            $product = $products->random();
            $reviewer = $users->where('id', '!=', $product->user_id)->random();

            // Check if review already exists
            $exists = \App\Models\Review::where('user_id', $reviewer->id)
                ->where('product_id', $product->id)
                ->exists();

            if (! $exists) {
                \App\Models\Review::factory()->create([
                    'user_id' => $reviewer->id,
                    'product_id' => $product->id,
                ]);
            }
        }
    }

    private function createFavourites($users): void
    {
        $products = \App\Models\Product::all();

        // Each user favourites 3-5 random products
        foreach ($users as $user) {
            $favouriteCount = rand(3, 5);
            $randomProducts = $products->random($favouriteCount);

            foreach ($randomProducts as $product) {
                \App\Models\Favourite::firstOrCreate([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                ]);
            }
        }
    }

    private function createConversations($users): void
    {
        $products = \App\Models\Product::all();

        // Create 15 conversations
        foreach (range(1, 15) as $i) {
            $product = $products->random();
            $userOne = $users->random();
            $userTwo = $users->where('id', '!=', $userOne->id)->random();

            // Check if conversation already exists
            $exists = \App\Models\Conversation::where('product_id', $product->id)
                ->where(function ($query) use ($userOne, $userTwo) {
                    $query->where(function ($q) use ($userOne, $userTwo) {
                        $q->where('user_one_id', $userOne->id)
                            ->where('user_two_id', $userTwo->id);
                    })->orWhere(function ($q) use ($userOne, $userTwo) {
                        $q->where('user_one_id', $userTwo->id)
                            ->where('user_two_id', $userOne->id);
                    });
                })
                ->exists();

            if (! $exists) {
                $conversation = \App\Models\Conversation::factory()->create([
                    'user_one_id' => $userOne->id,
                    'user_two_id' => $userTwo->id,
                    'product_id' => $product->id,
                ]);

                // Create 2-5 messages per conversation
                $messageCount = rand(2, 5);
                foreach (range(1, $messageCount) as $j) {
                    $senderId = $j % 2 === 0 ? $userOne->id : $userTwo->id;

                    \App\Models\Message::factory()->create([
                        'conversation_id' => $conversation->id,
                        'sender_id' => $senderId,
                    ]);
                }

                // Update last_message_at
                $lastMessage = $conversation->messages()->latest()->first();
                if ($lastMessage) {
                    $conversation->update(['last_message_at' => $lastMessage->created_at]);
                }
            }
        }
    }
}
