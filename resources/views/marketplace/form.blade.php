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
    <fieldset class="photo-editor" data-photo-editor>
        <legend>Listing photos (optional)</legend>
        <p class="field-hint">Add up to 8 photos, each up to 4 MB (JPG, PNG or WebP). Choose the primary photo shown in search results. New photos are added to your existing ones.</p>
        <div class="photo-grid" data-existing-photos>
            @foreach($listing->images as $image)
                <div class="photo-tile" data-existing-photo>
                    <img src="{{ asset('storage/'.$image->image_path) }}" alt="Existing listing photo {{ $loop->iteration }}">
                    <label class="photo-choice"><input type="radio" name="primary_image" value="existing:{{ $image->id }}" @checked(old('primary_image', $image->is_primary ? 'existing:'.$image->id : '') === 'existing:'.$image->id)> Primary photo</label>
                    <label class="photo-choice"><input type="checkbox" name="remove_images[]" value="{{ $image->id }}" data-remove-photo @checked(in_array($image->id, old('remove_images', [])))> Remove photo</label>
                </div>
            @endforeach
        </div>
        <label>Add photos<input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp" data-image-input></label>
        <p class="field-hint" data-photo-message role="status">If you remove the primary photo, another remaining photo becomes primary. Without photos, a category illustration is shown.</p>
        <div class="photo-grid" data-new-photos></div>
        <noscript><p class="field-hint">The first new photo becomes primary if no existing primary photo remains. Enable JavaScript to preview and choose a new photo as primary.</p></noscript>
    </fieldset>
    <div class="form-actions"><a href="{{ route('dashboard') }}" class="button secondary">Cancel</a><button class="button">{{ $listing->exists ? 'Save changes' : 'Publish listing' }} ↗</button></div>
</form>
@endsection
