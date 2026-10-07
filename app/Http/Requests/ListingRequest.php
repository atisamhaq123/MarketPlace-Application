<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'status' => ['required', Rule::in(['available', 'sold'])],
        ];
    }
}
