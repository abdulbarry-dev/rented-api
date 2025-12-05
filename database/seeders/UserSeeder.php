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
        $avatars = ['avatars/user_1.webp', 'avatars/user_2.jpeg', 'avatars/user_3.png'];

        // Create main test user with password 'password'
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'avatar' => $avatars[0],
            'verification_status' => 'verified',
        ]);

        // Create additional users with avatars
        User::factory(9)->create()->each(function ($user, $index) use ($avatars) {
            $avatarIndex = $index % count($avatars);
            $user->update(['avatar' => $avatars[$avatarIndex]]);
        });

        $this->command->info('✅ Created 10 users (test@example.com / password)');
    }
}
