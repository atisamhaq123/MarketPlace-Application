<?php

namespace Database\Factories;

use App\Models\Listing;
use Illuminate\Database\Eloquent\Factories\Factory;

class ListingImageFactory extends Factory
{
    public function definition(): array
    {
        return ['listing_id' => Listing::factory(), 'image_path' => 'listings/'.fake()->uuid().'.jpg', 'is_primary' => false, 'sort_order' => 0];
    }
}
