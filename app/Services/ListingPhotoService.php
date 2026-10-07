<?php

namespace App\Services;

use App\Models\Listing;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class ListingPhotoService
{
    /** @param array<int, UploadedFile> $uploads */
    public function save(Listing $listing, array $data, array $uploads, array $removeIds, ?string $primary, bool $replace = false): void
    {
        $uploadedPaths = [];
        $removedPaths = [];
        try {
            DB::transaction(function () use ($listing, $data, $uploads, $removeIds, $primary, $replace, &$uploadedPaths, &$removedPaths): void {
                if ($listing->exists) {
                    $locked = Listing::whereKey($listing->id)->lockForUpdate()->firstOrFail();
                    $listing->setRawAttributes($locked->getAttributes(), true);
                }
                $listing->fill($data)->save();
                if ($listing->image_path && ! $listing->images()->exists()) {
                    $listing->images()->create(['image_path' => $listing->image_path, 'is_primary' => true]);
                }
                $images = $listing->images()->get();
                $removed = $replace ? $images : $images->whereIn('id', $removeIds);
                $remaining = $images->whereNotIn('id', $removed->modelKeys())->values();
                if ($remaining->count() + count($uploads) > 8) {
                    throw ValidationException::withMessages(['images' => 'A listing can have up to 8 photos.']);
                }
                foreach ($removed as $image) {
                    $removedPaths[] = $image->image_path;
                    $image->delete();
                }
                $newImages = [];
                foreach ($uploads as $index => $upload) {
                    $path = $upload->store('listings', 'public');
                    if (! $path) {
                        throw new RuntimeException('The photo could not be saved. Please try again.');
                    }
                    $uploadedPaths[] = $path;
                    $image = $listing->images()->create(['image_path' => $path, 'sort_order' => $remaining->count()]);
                    $newImages[$index] = $image;
                    $remaining->push($image);
                }
                $selected = null;
                if ($primary) {
                    [$type, $id] = explode(':', $primary);
                    $selected = $type === 'new' ? ($newImages[(int) $id] ?? null) : $remaining->firstWhere('id', (int) $id);
                }
                $selected ??= $remaining->firstWhere('is_primary', true) ?? $remaining->first();
                foreach ($remaining as $position => $image) {
                    $image->update(['sort_order' => $position, 'is_primary' => $image->id === $selected?->id]);
                }
                $listing->forceFill(['image_path' => $selected?->image_path])->save();
                $listing->unsetRelation('images');
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($uploadedPaths);
            throw $exception;
        }
        Storage::disk('public')->delete($removedPaths);
    }

    public function delete(Listing $listing): void
    {
        $paths = DB::transaction(function () use ($listing): array {
            $locked = Listing::whereKey($listing->id)->lockForUpdate()->firstOrFail();
            $paths = $locked->images()->pluck('image_path')->all();
            if ($locked->image_path) {
                $paths[] = $locked->image_path;
            }
            $locked->delete();

            return array_unique($paths);
        });
        Storage::disk('public')->delete($paths);
    }
}
