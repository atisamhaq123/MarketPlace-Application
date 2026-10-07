@extends('layouts.app')
@section('title', $listing->exists ? 'Edit listing' : 'Create a listing')
@section('content')
<div class="page-heading"><div class="eyebrow">GIVE IT A NEW BEGINNING</div><h1>{{ $listing->exists ? 'Edit your listing' : 'What are you passing on?' }}</h1><p>A few details are all it takes to help your item find its next home.</p></div>
<form class="panel listing-form" method="POST" enctype="multipart/form-data" action="{{ $listing->exists ? route('listings.update', $listing) : route('listings.store') }}">
    @csrf @if($listing->exists)@method('PUT')@endif
    <label>Listing title<input name="title" value="{{ old('title', $listing->title) }}" placeholder="e.g. Solid oak lounge chair" required maxlength="150"></label>
    <div class="form-grid"><label>Category<select name="category_id" required><option value="">Choose a category</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $listing->category_id) == $category->id)>{{ $category->name }}</option>@endforeach</select></label>
    <label>Condition<select name="condition">@foreach(['new' => 'New', 'like_new' => 'Like new', 'used' => 'Used'] as $value => $label)<option value="{{ $value }}" @selected(old('condition', $listing->condition) === $value)>{{ $label }}</option>@endforeach</select></label></div>
    <label>Description<textarea name="description" rows="5" required minlength="20" maxlength="5000" placeholder="Tell buyers about your item, its condition, and pickup arrangements.">{{ old('description', $listing->description) }}</textarea><span class="field-hint">At least 20 characters. Mention any wear or imperfections.</span></label>
    <div class="form-grid"><label>Price (PKR)<input type="number" name="price" value="{{ old('price', $listing->price) }}" min="1" max="9999999999.99" step="0.01" placeholder="8500" required></label>
    <label>City / area<input name="location" value="{{ old('location', $listing->location) }}" placeholder="Karachi" required maxlength="100"></label></div>
    <div class="form-grid"><label>Contact phone<input name="contact_phone" type="tel" value="{{ old('contact_phone', $listing->contact_phone) }}" placeholder="+92 300 1234567" required maxlength="30"><span class="field-hint">Visible to logged-in buyers on available listings.</span></label>
    <label>Listing status<select name="status"><option value="available" @selected(old('status', $listing->status) === 'available')>Available</option><option value="sold" @selected(old('status', $listing->status) === 'sold')>Sold</option></select></label></div>
    <label>Photo (optional)<input type="file" name="image" accept="image/jpeg,image/png,image/webp" data-image-input><span class="field-hint">JPG, PNG or WebP, up to 4 MB. A category illustration appears if you don’t add a photo.</span></label>
    <img class="upload-preview" data-image-preview src="{{ $listing->image_path ? asset('storage/'.$listing->image_path) : '' }}" alt="Listing photo preview" @if(!$listing->image_path) hidden @endif>
    <div class="form-actions"><a href="{{ route('dashboard') }}" class="button secondary">Cancel</a><button class="button">{{ $listing->exists ? 'Save changes' : 'Publish listing' }} ↗</button></div>
</form>
@endsection