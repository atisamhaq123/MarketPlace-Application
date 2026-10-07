@props(['listing'])
<a href="{{ route('listings.show', $listing) }}" class="listing-card">
    <div class="product-image tone-{{ $listing->category->icon }}">
        @if($listing->image_path)<img src="{{ asset('storage/'.$listing->image_path) }}" alt="{{ $listing->title }}" loading="lazy">
        @else<x-product-art :icon="$listing->category->icon"/><span class="illustration-label">Illustration</span>@endif
        <span class="condition">{{ str_replace('_', ' ', ucfirst($listing->condition)) }}</span>
        <span class="card-arrow" aria-hidden="true">↗</span>
    </div>
    <div class="card-body"><div class="card-category">{{ $listing->category->name }}</div>
        <h3>{{ $listing->title }}</h3><strong class="price"><span>Rs</span> {{ number_format($listing->price) }}</strong>
        <div class="card-meta"><span>⌖ {{ $listing->location }}</span><span>{{ $listing->created_at->diffForHumans(null, true, true) }} ago</span></div>
    </div>
</a>