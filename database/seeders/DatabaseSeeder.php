<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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

        // Create test users first
        User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Seed categories and products
        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
        ]);
    }
}
