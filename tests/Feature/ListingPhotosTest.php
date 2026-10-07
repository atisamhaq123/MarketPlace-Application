<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\User;
use App\Services\ListingPhotoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class ListingPhotosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function listingData(?Listing $listing = null): array
    {
        return [
            'title' => 'Multiple photo headphones', 'description' => 'Excellent headphones with several photos of their condition.',
            'category_id' => $listing?->category_id ?? Category::factory()->create()->id,
            'price' => 8500, 'location' => 'Karachi', 'condition' => 'used',
            'contact_phone' => '+923001234567', 'status' => 'available',
        ];
    }

    private function addPhoto(Listing $listing, bool $primary = false): ListingImage
    {
        $image = ListingImage::factory()->for($listing)->create(['is_primary' => $primary, 'sort_order' => $listing->images()->count()]);
        Storage::disk('public')->put($image->image_path, 'photo');
        if ($primary) {
            $listing->update(['image_path' => $image->image_path]);
        }

        return $image;
    }

    public function test_multiple_uploads_use_selected_primary_in_cards_and_gallery(): void
    {
        $data = $this->listingData();
        $data['images'] = [UploadedFile::fake()->image('front.jpg'), UploadedFile::fake()->image('side.png'), UploadedFile::fake()->image('back.jpg')];
        $data['primary_image'] = 'new:1';
        $this->actingAs(User::factory()->create())->post('/listings', $data)->assertRedirect()->assertSessionHasNoErrors();
        $listing = Listing::firstOrFail();
        $images = $listing->images()->get();
        $this->assertCount(3, $images);
        $this->assertSame($images[1]->image_path, $listing->image_path);
        $this->assertSame(1, $images->where('is_primary', true)->count());
        foreach ($images as $image) {
            Storage::disk('public')->assertExists($image->image_path);
        }
        $this->get('/')->assertOk()->assertSee(asset('storage/'.$images[1]->image_path))->assertDontSee(asset('storage/'.$images[0]->image_path));
        $page = $this->get(route('listings.show', $listing))->assertOk()->assertSee('data-listing-gallery', false);
        foreach ($images as $image) {
            $page->assertSee(asset('storage/'.$image->image_path));
        }
        $this->get(route('listings.edit', $listing))->assertOk()->assertSee('multiple', false)->assertSee('existing:'.$images[1]->id);
    }

    public function test_first_uploaded_photo_becomes_primary_by_default(): void
    {
        $data = $this->listingData();
        $data['images'] = [UploadedFile::fake()->image('one.jpg'), UploadedFile::fake()->image('two.jpg')];
        $this->actingAs(User::factory()->create())->post('/listings', $data)->assertSessionHasNoErrors();
        $listing = Listing::firstOrFail();
        $this->assertSame($listing->images()->first()->image_path, $listing->image_path);
    }

    public function test_edit_can_add_photos_and_switch_between_existing_and_new_primary(): void
    {
        $listing = Listing::factory()->create();
        $first = $this->addPhoto($listing, true);
        $second = $this->addPhoto($listing);
        $this->actingAs($listing->user);
        $data = $this->listingData($listing);
        $data['images'] = [UploadedFile::fake()->image('new.jpg')];
        $data['primary_image'] = 'new:0';
        $this->put(route('listings.update', $listing), $data)->assertSessionHasNoErrors();
        $listing->refresh();
        $this->assertSame(3, $listing->images()->count());
        $this->assertNotSame($first->image_path, $listing->image_path);
        Storage::disk('public')->assertExists($first->image_path);
        $data = $this->listingData($listing);
        $data['primary_image'] = 'existing:'.$second->id;
        $this->put(route('listings.update', $listing), $data)->assertSessionHasNoErrors();
        $this->assertSame($second->image_path, $listing->refresh()->image_path);
        $this->assertSame(1, $listing->images()->where('is_primary', true)->count());
    }

    public function test_removing_primary_selects_fallback_and_removing_all_restores_illustration(): void
    {
        $listing = Listing::factory()->create();
        $first = $this->addPhoto($listing, true);
        $second = $this->addPhoto($listing);
        $data = $this->listingData($listing);
        $data['remove_images'] = [$first->id];
        $data['primary_image'] = 'existing:'.$first->id;
        $this->actingAs($listing->user)->put(route('listings.update', $listing), $data)->assertSessionHasNoErrors();
        $this->assertSame($second->image_path, $listing->refresh()->image_path);
        Storage::disk('public')->assertMissing($first->image_path);
        $data['remove_images'] = [$second->id];
        $data['primary_image'] = 'existing:'.$second->id;
        $this->put(route('listings.update', $listing), $data)->assertSessionHasNoErrors();
        $this->assertNull($listing->refresh()->image_path);
        $this->assertSame(0, $listing->images()->count());
        $this->get(route('listings.show', $listing))->assertOk()->assertSee('no photo provided');
    }

    public function test_ordinary_edit_and_appending_photos_preserve_current_primary(): void
    {
        $listing = Listing::factory()->create();
        $first = $this->addPhoto($listing);
        $primary = $this->addPhoto($listing, true);
        $data = $this->listingData($listing);
        $this->actingAs($listing->user)->put(route('listings.update', $listing), $data)->assertSessionHasNoErrors();
        $this->assertTrue($primary->refresh()->is_primary);
        $data['images'] = [UploadedFile::fake()->image('extra.jpg')];
        $this->put(route('listings.update', $listing), $data)->assertSessionHasNoErrors();
        $this->assertSame($primary->image_path, $listing->refresh()->image_path);
        $this->assertTrue($primary->refresh()->is_primary);
        $this->assertFalse($first->refresh()->is_primary);
        $this->assertSame(1, $listing->images()->where('is_primary', true)->count());
    }

    public function test_eight_photo_limit_includes_retained_photos_and_allows_replacement(): void
    {
        $listing = Listing::factory()->create();
        $first = $this->addPhoto($listing, true);
        for ($index = 0; $index < 7; $index++) {
            $this->addPhoto($listing);
        }
        $data = $this->listingData($listing);
        $data['images'] = [UploadedFile::fake()->image('ninth.jpg')];
        $this->actingAs($listing->user)->put(route('listings.update', $listing), $data)->assertSessionHasErrors('images');
        $this->assertSame(8, $listing->images()->count());
        $data['remove_images'] = [$first->id];
        $data['primary_image'] = 'new:0';
        $this->put(route('listings.update', $listing), $data)->assertSessionHasNoErrors();
        $this->assertSame(8, $listing->images()->count());
        Storage::disk('public')->assertMissing($first->image_path);
        $this->assertSame(1, $listing->images()->where('is_primary', true)->count());
    }

    public function test_foreign_photo_ids_and_invalid_primary_indices_are_rejected(): void
    {
        $listing = Listing::factory()->create();
        $ownPhoto = $this->addPhoto($listing, true);
        $foreignPhoto = $this->addPhoto(Listing::factory()->create(), true);
        $data = $this->listingData($listing);
        $data['remove_images'] = [$foreignPhoto->id];
        $data['primary_image'] = 'existing:'.$foreignPhoto->id;
        $this->actingAs($listing->user)->put(route('listings.update', $listing), $data)->assertSessionHasErrors('remove_images.0');
        unset($data['remove_images']);
        $this->put(route('listings.update', $listing), $data)->assertSessionHasErrors('primary_image');
        $data['primary_image'] = 'new:99';
        $this->put(route('listings.update', $listing), $data)->assertSessionHasErrors('primary_image');
        $this->assertSame($ownPhoto->image_path, $listing->refresh()->image_path);
        Storage::disk('public')->assertExists($foreignPhoto->image_path);
    }

    public function test_invalid_oversized_and_excess_uploads_are_rejected(): void
    {
        $this->actingAs(User::factory()->create());
        $data = $this->listingData();
        $data['images'] = [UploadedFile::fake()->create('script.php', 1, 'text/plain'), UploadedFile::fake()->image('large.jpg')->size(4097)];
        $this->post('/listings', $data)->assertSessionHasErrors(['images.0', 'images.1']);
        $data['images'] = array_map(fn ($index) => UploadedFile::fake()->image($index.'.jpg'), range(1, 9));
        $this->post('/listings', $data)->assertSessionHasErrors('images');
        $this->assertDatabaseCount('listings', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_deleting_listing_removes_all_photo_records_and_files(): void
    {
        $listing = Listing::factory()->create();
        $first = $this->addPhoto($listing, true);
        $second = $this->addPhoto($listing);
        $this->actingAs($listing->user)->delete(route('listings.destroy', $listing))->assertRedirect('/dashboard');
        $this->assertDatabaseCount('listing_images', 0);
        Storage::disk('public')->assertMissing([$first->image_path, $second->image_path]);
    }

    public function test_failed_upload_rolls_back_changes_and_cleans_new_files(): void
    {
        $listing = Listing::factory()->create();
        $old = $this->addPhoto($listing, true);
        $failedUpload = Mockery::mock(UploadedFile::class);
        $failedUpload->shouldReceive('store')->once()->with('listings', 'public')->andReturn(false);
        try {
            app(ListingPhotoService::class)->save($listing, $this->listingData($listing), [UploadedFile::fake()->image('new.jpg'), $failedUpload], [$old->id], null);
            $this->fail('Expected the failed upload to stop the update.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('could not be saved', $exception->getMessage());
        }
        $this->assertSame($old->image_path, $listing->refresh()->image_path);
        $this->assertSame(1, $listing->images()->count());
        $this->assertSame([$old->image_path], Storage::disk('public')->allFiles());
    }

    public function test_migration_preserves_legacy_listing_photo_as_primary(): void
    {
        $listing = Listing::factory()->create(['image_path' => 'listings/legacy.jpg']);
        $migration = require database_path('migrations/2026_10_07_053732_create_listing_images_table.php');
        $migration->down();
        $migration->up();
        $image = $listing->images()->firstOrFail();
        $this->assertSame('listings/legacy.jpg', $image->image_path);
        $this->assertTrue($image->is_primary);
        $this->assertSame('listings/legacy.jpg', $listing->refresh()->image_path);
    }
}
