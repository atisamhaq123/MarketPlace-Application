@extends('layouts.app')
@section('content')
<section class="hero">
    <div class="hero-copy"><div class="eyebrow"><span class="live-dot"></span> YOUR LOCAL MARKETPLACE</div>
        <h1>Great finds.<br>New beginnings<span>.</span></h1>
        <p>Discover something you’ll love. Pass on something you’ve loved.<br class="desktop-only"> It all starts right here, in your community.</p>
        <a href="#discover" class="button">Find your next favorite <span>↗</span></a>
        <div class="hero-note"><span class="little-circle">↻</span> A second life for things. More value for you.</div>
    </div>
    <div class="hero-visual" aria-hidden="true">
        <div class="orbit"></div><div class="hero-label">GOOD FINDS, CLOSE BY <span>✦</span></div>
        <div class="float-card chair"><x-product-art icon="home"/><div>For your favorite corner <strong>Home & Living ↗</strong></div></div>
        <div class="float-card headphones"><x-product-art icon="tech"/><div>A little more good sound <strong>Electronics ↗</strong></div></div>
        <div class="round-sticker">PRE-LOVED<br><strong>RE-LOVED</strong><span>✳</span></div>
    </div>
</section>
<section class="search-section" id="discover">
    <form action="{{ route('home') }}" class="search-form">
        <label class="search-field"><span aria-hidden="true">⌕</span><span class="sr-only">Search listings</span><input name="q" placeholder="What are you looking for?" value="{{ request('q') }}" maxlength="100"></label>
        <label class="location-field"><span aria-hidden="true">⌖</span><span class="sr-only">City</span><input name="location" placeholder="All locations" value="{{ request('location') }}" maxlength="100"></label>
        @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
        <button class="button">Search <span>→</span></button>
    </form>
</section>
<section class="categories-section">
    <div class="section-heading"><h2>Explore by category</h2><a href="{{ route('home') }}#discover">View all <span>↗</span></a></div>
    <div class="category-grid">
        @foreach($categories as $category)
        <a class="category-item {{ request('category') === $category->slug ? 'selected' : '' }}" href="{{ route('home', array_filter(['category' => $category->slug, 'q' => request('q'), 'location' => request('location')])) }}#discover">
            <div class="category-icon tone-{{ $category->icon }}"><x-product-art :icon="$category->icon"/></div><strong>{{ $category->name }}</strong><span>{{ $category->listings_count }} listings</span>
        </a>
        @endforeach
    </div>
</section>
<section class="listings-section">
    <div class="section-heading listing-heading"><div><div class="eyebrow">A LITTLE DISCOVERY GOES A LONG WAY</div><h2>{{ request()->hasAny(['q', 'category', 'location']) ? 'Your local finds' : 'Fresh finds for you' }} <span class="count">{{ $listings->total() }}</span></h2></div>
        <form action="{{ route('home') }}" class="sort-form">
            @foreach(['q', 'location', 'category'] as $filter)@if(request($filter))<input type="hidden" name="{{ $filter }}" value="{{ request($filter) }}">@endif @endforeach
            <label for="sort">Sort by</label><select name="sort" id="sort" data-auto-submit><option value="newest" @selected(request('sort', 'newest') === 'newest')>Newest first</option><option value="price_asc" @selected(request('sort') === 'price_asc')>Price: low to high</option><option value="price_desc" @selected(request('sort') === 'price_desc')>Price: high to low</option></select><noscript><button>Apply</button></noscript>
        </form>
    </div>
    @if(request()->hasAny(['q', 'category', 'location']))<div class="filter-note">Showing filtered listings. <a href="{{ route('home') }}#discover">Clear filters ×</a></div>@endif
    <div class="listing-grid">@forelse($listings as $listing)<x-listing-card :listing="$listing"/>@empty
        <div class="empty-state"><span>⌕</span><h3>No finds just yet</h3><p>Try a different search or explore all categories.</p><a href="{{ route('home') }}" class="button">Explore all listings</a></div>
    @endforelse</div>
    @include('marketplace.pagination', ['paginator' => $listings])
</section>
<section class="sell-banner"><div class="sell-symbol">↻</div><div><div class="eyebrow">MAKE ROOM FOR WHAT’S NEXT</div><h2>Someone’s next favorite could be yours.</h2><p>Give your unused things a new home. Listing is free and takes just a minute.</p></div><a href="{{ route('listings.create') }}" class="button">Create a listing ↗</a></section>
@endsection