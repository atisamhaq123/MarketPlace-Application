<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $listing = $this->route('listing');

        return $listing ? $this->user()->can('update', $listing) : $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:1', 'max:9999999999.99', 'decimal:0,2'],
            'location' => ['required', 'string', 'max:100'],
            'condition' => ['required', Rule::in(['new', 'like_new', 'used'])],
            'contact_phone' => ['required', 'string', 'regex:/^\\+?[0-9 ()-]{7,30}$/'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'images' => ['nullable', 'array', 'max:8', Rule::prohibitedIf($this->hasFile('image'))],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_images' => ['nullable', 'array', 'max:8'],
            'remove_images.*' => ['integer', 'distinct', Rule::exists('listing_images', 'id')->where('listing_id', $this->route('listing')?->id ?? 0)],
            'primary_image' => ['nullable', 'string', 'regex:/^(existing|new):[0-9]+$/'],
            'status' => ['required', Rule::in(['available', 'sold'])],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $listing = $this->route('listing');
            $existing = $listing?->images()->get() ?? collect();
            $removed = $this->input('remove_images', []);
            $uploads = $this->file('images', []);
            $retained = $existing->whereNotIn('id', $removed)->count();
            if ($existing->isEmpty() && $listing?->image_path) {
                $retained = 1;
            }
            if (! $this->hasFile('image') && $retained + count($uploads) > 8) {
                $validator->errors()->add('images', 'A listing can have up to 8 photos. Remove an existing photo before adding more.');
            }

            $primary = $this->input('primary_image');
            if ($primary) {
                [$type, $id] = explode(':', $primary);
                $valid = $type === 'existing' ? $existing->contains('id', (int) $id) : array_key_exists((int) $id, $uploads);
                if (! $valid) {
                    $validator->errors()->add('primary_image', 'Choose a primary photo belonging to this listing.');
                }
            }
        }];
    }
}
