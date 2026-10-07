@extends('layouts.app')
@section('title', 'My listings')
@section('content')
<div class="page-heading heading-row"><div><div class="eyebrow">YOUR LITTLE CORNER</div><h1>Hello, {{ auth()->user()->name }}.</h1><p>Manage your finds and make room for something new.</p></div><a href="{{ route('listings.create') }}" class="button">＋ Create a listing</a></div>
<div class="panel dashboard-panel"><div class="section-heading"><h2>My listings <span class="count">{{ $listings->total() }}</span></h2></div>
@forelse($listings as $listing)
<div class="dashboard-row"><div class="dashboard-thumb tone-{{ $listing->category->icon }}">@if($listing->image_path)<img src="{{ asset('storage/'.$listing->image_path) }}" alt="">@else<x-product-art :icon="$listing->category->icon"/>@endif</div>
<div class="dashboard-info"><a href="{{ route('listings.show', $listing) }}"><h3>{{ $listing->title }}</h3></a><span>Rs {{ number_format($listing->price) }} · {{ $listing->location }}</span></div>
<span class="pill {{ $listing->status === 'sold' ? 'sold' : '' }}">{{ ucfirst($listing->status) }}</span>
<div class="row-actions"><a class="button secondary small" href="{{ route('listings.edit', $listing) }}">Edit</a><form method="POST" action="{{ route('listings.destroy', $listing) }}" data-confirm="Delete this listing? This cannot be undone.">@csrf @method('DELETE')<button class="text-button danger">Delete</button></form></div>
</div>
@empty<div class="empty-state"><span>↻</span><h3>Your first listing starts here</h3><p>That thing you no longer use could be someone’s next favorite.</p><a href="{{ route('listings.create') }}" class="button">Create a listing</a></div>@endforelse
@include('marketplace.pagination', ['paginator' => $listings])
</div>
@endsection