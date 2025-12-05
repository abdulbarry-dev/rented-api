<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class PrepareTestImages extends Seeder
{
    /**
     * Prepare test images for seeding by copying a source image
     * to avatar and product directories
     */
    public function run(): void
    {
        // Source image path (user should place their image here)
        $sourceImage = storage_path('app/seed-images/laptop.jpg');

        if (!File::exists($sourceImage)) {
            $this->command->error('❌ Source image not found at: ' . $sourceImage);
            $this->command->info('📝 Please place your laptop image at: storage/app/seed-images/laptop.jpg');
            $this->command->info('   Supported formats: jpg, jpeg, png, webp');
            return;
        }

        // Ensure public disk directories exist
        $publicDisk = Storage::disk('public');
        
        $directories = [
            'avatars',
            'products/images',
        ];

        foreach ($directories as $directory) {
            if (!$publicDisk->exists($directory)) {
                $publicDisk->makeDirectory($directory);
                $this->command->info("✅ Created directory: {$directory}");
            }
        }

        // Copy source image to avatar directory
        $avatarPath = storage_path('app/public/avatars/laptop_test.jpg');
        File::copy($sourceImage, $avatarPath);
        $this->command->info('✅ Copied laptop image to: storage/app/public/avatars/laptop_test.jpg');

        // Copy source image to products directory
        $productPath = storage_path('app/public/products/images/laptop_test.jpg');
        File::copy($sourceImage, $productPath);
        $this->command->info('✅ Copied laptop image to: storage/app/public/products/images/laptop_test.jpg');

        $this->command->info('');
        $this->command->info('🎉 Test images prepared successfully!');
        $this->command->info('   You can now run: php artisan db:seed');
    }
}
