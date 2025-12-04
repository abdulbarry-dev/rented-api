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

        // Create 50 sample products
        \App\Models\Product::factory(50)->create([
            'user_id' => $users->random()->id,
            'category_id' => $categories->random()->id,
        ]);
    }
}
