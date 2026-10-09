<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Conversation> */
class ConversationFactory extends Factory
{
    /** @return array{listing_id: mixed, listing_title: mixed, buyer_id: mixed, seller_id: mixed} */
    public function definition(): array
    {
        return [
            'listing_id' => Listing::factory(),
            'listing_title' => fn (array $attributes) => Listing::findOrFail($attributes['listing_id'])->title,
            'buyer_id' => User::factory(),
            'seller_id' => fn (array $attributes) => Listing::findOrFail($attributes['listing_id'])->user_id,
        ];
    }
}
