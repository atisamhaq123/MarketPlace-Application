<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase;

    private function listingData(Category $category): array
    {
        return [
            'title' => 'Oak lounge chair', 'description' => 'A carefully maintained chair ready for a new home.',
            'category_id' => $category->id, 'price' => 14500, 'location' => 'Karachi',
            'condition' => 'used', 'contact_phone' => '+923001234567', 'status' => 'available',
        ];
    }

    public function test_marketplace_filters_search_category_location_and_hides_sold_items(): void
    {
        $category = Category::factory()->create(['slug' => 'furniture']);
        $match = Listing::factory()->for($category)->create(['title' => 'Oak lounge chair', 'location' => 'Karachi']);
        $sold = Listing::factory()->for($category)->create(['title' => 'Sold oak chair', 'status' => 'sold']);
        $otherCity = Listing::factory()->for($category)->create(['title' => 'Lahore oak chair', 'location' => 'Lahore']);
        $otherCategory = Listing::factory()->create(['title' => 'Other oak chair']);
        $this->get('/?q=oak&category=furniture&location=Karachi')->assertOk()
            ->assertSee($match->title)->assertDontSee($sold->title)->assertDontSee($otherCity->title)->assertDontSee($otherCategory->title);
    }

    public function test_listings_sort_by_price_and_paginate(): void
    {
        Listing::factory()->count(13)->create(['price' => 5000]);
        Listing::factory()->create(['title' => 'Lowest price find', 'price' => 100]);
        Listing::factory()->create(['title' => 'Highest price find', 'price' => 9000]);
        $this->get('/?sort=price_asc')->assertOk()->assertSee('Lowest price find')->assertDontSee('Highest price find')->assertSee('Next');
        $this->get('/?sort=price_desc')->assertOk()->assertSee('Highest price find')->assertDontSee('Lowest price find');
    }

    public function test_guests_cannot_manage_listings_or_see_contact_phone(): void
    {
        $listing = Listing::factory()->create();
        $this->get('/listings/create')->assertRedirect('/login');
        $this->post('/listings', [])->assertRedirect('/login');
        $this->get(route('listings.show', $listing))->assertOk()->assertSee('Log in to contact seller')->assertDontSee($listing->contact_phone);
        $this->actingAs(User::factory()->create())->get(route('listings.show', $listing))->assertOk()->assertSee($listing->contact_phone);
    }

    public function test_registration_hashes_password_and_authenticates_user(): void
    {
        $this->post('/register', ['name' => 'New Seller', 'email' => 'new@example.com', 'password' => 'Password123!', 'password_confirmation' => 'Password123!'])->assertRedirect('/dashboard');
        $user = User::where('email', 'new@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('Password123!', $user->password));
        $this->assertAuthenticatedAs($user);
        $this->get('/dashboard')->assertOk()->assertSee('Your first listing starts here');
    }

    public function test_login_rejects_invalid_credentials_and_logout_ends_session(): void
    {
        $user = User::factory()->create();
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_seller_can_publish_listing_with_photo_and_cannot_spoof_owner(): void
    {
        Storage::fake('public');
        $seller = User::factory()->create();
        $other = User::factory()->create();
        $category = Category::factory()->create();
        $data = $this->listingData($category);
        $data['image'] = UploadedFile::fake()->image('chair.jpg');
        $data['user_id'] = $other->id;
        $this->actingAs($seller)->post('/listings', $data)->assertRedirect();
        $listing = Listing::firstOrFail();
        $this->assertSame($seller->id, $listing->user_id);
        Storage::disk('public')->assertExists($listing->image_path);
        $this->get('/dashboard')->assertOk()->assertSee($listing->title);
        $this->get(route('listings.edit', $listing))->assertOk()->assertSee('Edit your listing');
    }

    public function test_other_sellers_cannot_edit_update_or_delete_listing(): void
    {
        $listing = Listing::factory()->create();
        $this->actingAs(User::factory()->create());
        $this->get(route('listings.edit', $listing))->assertForbidden();
        $this->put(route('listings.update', $listing), $this->listingData($listing->category))->assertForbidden();
        $this->delete(route('listings.destroy', $listing))->assertForbidden();
        $this->assertDatabaseHas('listings', ['id' => $listing->id, 'title' => $listing->title]);
        $this->get('/dashboard')->assertOk()->assertDontSee($listing->title);
    }

    public function test_owner_can_replace_photo_mark_sold_and_delete_listing(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('listings/old.jpg', 'old image');
        $listing = Listing::factory()->create(['image_path' => 'listings/old.jpg']);
        $data = $this->listingData($listing->category);
        $data['status'] = 'sold';
        $data['image'] = UploadedFile::fake()->image('new.jpg');
        $this->actingAs($listing->user)->put(route('listings.update', $listing), $data)->assertRedirect('/dashboard');
        $listing->refresh();
        Storage::disk('public')->assertMissing('listings/old.jpg');
        Storage::disk('public')->assertExists($listing->image_path);
        $this->get('/')->assertDontSee($listing->title);
        $this->get(route('listings.show', $listing))->assertSee('This item has found a new home.');
        $this->delete(route('listings.destroy', $listing))->assertRedirect('/dashboard');
        Storage::disk('public')->assertMissing($listing->image_path);
        $this->assertDatabaseMissing('listings', ['id' => $listing->id]);
    }

    public function test_invalid_listing_values_and_unsafe_uploads_are_rejected(): void
    {
        $data = $this->listingData(Category::factory()->create());
        $data['price'] = -1;
        $data['category_id'] = 99999;
        $data['status'] = 'invalid';
        $data['contact_phone'] = 'not-a-phone';
        $data['image'] = UploadedFile::fake()->create('script.php', 1, 'text/plain');
        $this->actingAs(User::factory()->create())->post('/listings', $data)->assertSessionHasErrors(['price', 'category_id', 'status', 'contact_phone', 'image']);
        $this->assertDatabaseCount('listings', 0);
    }

    public function test_auth_forms_and_missing_listing_render_correctly(): void
    {
        $this->get('/login')->assertOk()->assertSee('Welcome back.');
        $this->get('/register')->assertOk()->assertSee('Join the neighborhood.');
        $this->get('/listings/9999')->assertNotFound();
    }
}
