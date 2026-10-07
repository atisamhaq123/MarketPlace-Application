<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }
        $seller = User::firstOrCreate(['email' => 'seller@example.com'], ['name' => 'Ayesha Khan', 'password' => 'MarketPlace123!']);
        $categories = [
            ['Electronics', 'electronics', 'tech'], ['Home & Living', 'home-living', 'home'],
            ['Fashion', 'fashion', 'fashion'], ['Sports & Outdoors', 'sports-outdoors', 'sports'],
            ['Books & Hobbies', 'books-hobbies', 'books'], ['Vehicles', 'vehicles', 'vehicles'],
        ];
        foreach ($categories as [$name, $slug, $icon]) {
            Category::firstOrCreate(['slug' => $slug], compact('name', 'icon'));
        }
        $products = [
            ['Wireless over-ear headphones', 'electronics', 8500, 'Karachi', 'like_new'],
            ['Solid oak lounge chair', 'home-living', 14500, 'Lahore', 'used'],
            ['Everyday canvas backpack', 'fashion', 2800, 'Islamabad', 'new'],
            ['Weekend city bicycle', 'vehicles', 32000, 'Karachi', 'used'],
            ['A collection of good reads', 'books-hobbies', 1800, 'Lahore', 'like_new'],
            ['Yoga mat & fitness essentials', 'sports-outdoors', 3500, 'Islamabad', 'new'],
            ['Compact wireless headphones', 'electronics', 6200, 'Rawalpindi', 'used'],
            ['Reading nook accent chair', 'home-living', 18500, 'Karachi', 'like_new'],
        ];
        foreach ($products as [$title, $slug, $price, $location, $condition]) {
            Listing::firstOrCreate(['user_id' => $seller->id, 'title' => $title], [
                'category_id' => Category::where('slug', $slug)->firstOrFail()->id,
                'description' => 'A lovely find, ready for a new home. Carefully maintained and in great working condition. Available for local pickup. Contact me to ask questions or arrange a time to take a look.',
                'price' => $price, 'location' => $location, 'condition' => $condition,
                'contact_phone' => '+923001234567', 'status' => 'available',
            ]);
        }
    }
}
