<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ListingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(), 'category_id' => Category::factory(),
            'title' => fake()->sentence(4), 'description' => fake()->paragraph(),
            'price' => fake()->numberBetween(500, 50000), 'location' => 'Karachi',
            'condition' => 'used', 'contact_phone' => '+923001234567', 'status' => 'available',
        ];
    }
}
