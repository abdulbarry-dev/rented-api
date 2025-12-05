<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Use laptop image for testing uploads
        $avatar = 'avatars/laptop_test.jpg';

        // Create main test user with laptop avatar
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'avatar_path' => $avatar,
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        // Create additional test users with same avatar for consistency
        User::factory(9)->create()->each(function ($user) use ($avatar) {
            $user->update([
                'avatar_path' => $avatar,
                'verification_status' => 'verified',
                'verified_at' => now(),
            ]);
        });

        $this->command->info('✅ Created 10 verified users with laptop avatar (test@example.com / password)');
    }
}
