@extends('layouts.app')
@section('title', $listing->title)
@section('content')
<div class="breadcrumbs"><a href="{{ route('home') }}">Explore</a><span>/</span><a href="{{ route('home', ['category' => $listing->category->slug]) }}">{{ $listing->category->name }}</a><span>/</span>Listing</div>
<div class="detail-grid">
    <div class="detail-image product-image tone-{{ $listing->category->icon }}">
        @if($listing->image_path)<img src="{{ asset('storage/'.$listing->image_path) }}" alt="{{ $listing->title }}">@else<x-product-art :icon="$listing->category->icon"/><span class="illustration-label">Category illustration · no photo provided</span>@endif
    </div>
    <div class="detail-copy"><div class="eyebrow">{{ $listing->category->name }}</div><h1>{{ $listing->title }}</h1>
        <div class="detail-price">Rs {{ number_format($listing->price) }}</div>
        <div class="detail-tags"><span class="pill">{{ str_replace('_', ' ', ucfirst($listing->condition)) }}</span><span class="pill {{ $listing->status === 'sold' ? 'sold' : '' }}">{{ ucfirst($listing->status) }}</span><span>⌖ {{ $listing->location }}</span></div>
        <hr><h3>A little about this find</h3><p class="description">{{ $listing->description }}</p>
        <div class="seller-card"><div class="avatar">{{ mb_substr($listing->user->name, 0, 1) }}</div><div><strong>{{ $listing->user->name }}</strong><span>Member since {{ $listing->user->created_at->format('M Y') }}</span></div></div>
        @if($listing->status === 'available')
            @auth<a href="tel:{{ preg_replace('/[^+0-9]/', '', $listing->contact_phone) }}" class="button full">Call seller · {{ $listing->contact_phone }}</a>
            @else<a href="{{ route('login') }}" class="button full">Log in to contact seller ↗</a>@endauth
            <p class="small-note">Arrange pickup directly with the seller. Payments are handled in person.</p>
        @else<div class="alert">This item has found a new home.</div>@endif
        @can('update', $listing)<a href="{{ route('listings.edit', $listing) }}" class="button secondary full">Edit your listing</a>@endcan
    </div>
</div>
@endsection