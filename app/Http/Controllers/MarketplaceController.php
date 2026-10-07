<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListingRequest;
use App\Models\Category;
use App\Models\Listing;
use App\Services\ListingPhotoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'exists:categories,slug'],
            'location' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', Rule::in(['newest', 'price_asc', 'price_desc'])],
        ]);
        $query = Listing::with(['category', 'user'])->where('status', 'available')
            ->when($filters['q'] ?? null, fn ($query, $search) => $query->where(fn ($query) => $query->where('title', 'like', '%'.$search.'%')->orWhere('description', 'like', '%'.$search.'%')))
            ->when($filters['category'] ?? null, fn ($query, $slug) => $query->whereHas('category', fn ($query) => $query->where('slug', $slug)))
            ->when($filters['location'] ?? null, fn ($query, $location) => $query->where('location', 'like', '%'.$location.'%'));
        match ($filters['sort'] ?? 'newest') {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            default => $query->latest(),
        };

        return view('marketplace.index', [
            'listings' => $query->orderByDesc('id')->paginate(12)->withQueryString(),
            'categories' => Category::withCount(['listings' => fn ($query) => $query->where('status', 'available')])->orderBy('id')->get(),
            'totalListings' => Listing::where('status', 'available')->count(),
        ]);
    }

    public function show(Listing $listing): View
    {
        $listing->load(['category', 'user', 'images']);

        return view('marketplace.show', compact('listing'));
    }

    public function dashboard(Request $request): View
    {
        return view('marketplace.dashboard', ['listings' => $request->user()->listings()->with('category')->latest()->paginate(10)]);
    }

    public function create(): View
    {
        return view('marketplace.form', ['listing' => new Listing(['status' => 'available', 'condition' => 'used']), 'categories' => Category::all()]);
    }

    public function edit(Listing $listing): View
    {
        Gate::authorize('update', $listing);
        $listing->load('images');

        return view('marketplace.form', ['listing' => $listing, 'categories' => Category::all()]);
    }

    public function store(ListingRequest $request, ListingPhotoService $photos): RedirectResponse
    {
        $listing = $request->user()->listings()->make();
        $photos->save($listing, $request->safe()->except(['image', 'images', 'remove_images', 'primary_image']),
            $request->hasFile('image') ? [$request->file('image')] : $request->file('images', []),
            $request->input('remove_images', []), $request->input('primary_image'), $request->hasFile('image'));

        return redirect()->route('listings.show', $listing)->with('success', 'Your listing is published.');
    }

    public function update(ListingRequest $request, Listing $listing, ListingPhotoService $photos): RedirectResponse
    {
        $photos->save($listing, $request->safe()->except(['image', 'images', 'remove_images', 'primary_image']),
            $request->hasFile('image') ? [$request->file('image')] : $request->file('images', []),
            $request->input('remove_images', []), $request->input('primary_image'), $request->hasFile('image'));

        return redirect()->route('dashboard')->with('success', 'Listing updated.');
    }

    public function destroy(Listing $listing, ListingPhotoService $photos): RedirectResponse
    {
        Gate::authorize('delete', $listing);
        $photos->delete($listing);

        return redirect()->route('dashboard')->with('success', 'Listing deleted.');
    }
}
