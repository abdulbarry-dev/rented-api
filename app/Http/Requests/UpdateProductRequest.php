<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => 'sometimes|integer|exists:categories,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:5000',
            'price_per_day' => 'sometimes|numeric|min:1|max:999999.99',
            'price_per_week' => 'nullable|numeric|min:1|max:999999.99',
            'price_per_month' => 'nullable|numeric|min:1|max:999999.99',
            'is_for_sale' => 'sometimes|boolean',
            'sale_price' => 'nullable|numeric|min:1|max:999999.99',
            'is_available' => 'sometimes|boolean',
            'location_address' => 'nullable|string|max:255',
            'location_city' => 'nullable|string|max:100',
            'location_state' => 'nullable|string|max:100',
            'location_country' => 'nullable|string|max:100',
            'location_zip' => 'nullable|string|max:20',
            'location_latitude' => 'nullable|numeric|between:-90,90',
            'location_longitude' => 'nullable|numeric|between:-180,180',
            'delivery_available' => 'nullable|boolean',
            'delivery_fee' => 'nullable|numeric|min:0|max:999999.99',
            'delivery_radius_km' => 'nullable|integer|min:0|max:10000',
            'pickup_available' => 'nullable|boolean',
            'product_condition' => 'nullable|string|in:new,like_new,good,fair,worn',
            'security_deposit' => 'nullable|numeric|min:0|max:999999.99',
            'min_rental_days' => 'nullable|integer|min:1|max:365',
            'max_rental_days' => 'nullable|integer|min:1|max:365',
            'thumbnail' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,jpg,png,webp|max:2048',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_id.exists' => 'Selected category does not exist.',
            'title.max' => 'Product title must not exceed 255 characters.',
            'description.max' => 'Description must not exceed 5000 characters.',
            'price_per_day.min' => 'Daily rental price must be at least 1.',
            'price_per_day.numeric' => 'Daily rental price must be a number.',
            'sale_price.min' => 'Sale price must be at least 1.',
            'thumbnail.image' => 'Thumbnail must be an image.',
            'thumbnail.max' => 'Thumbnail must not exceed 2MB.',
            'images.max' => 'You can upload a maximum of 5 images.',
            'images.*.image' => 'All uploaded files must be images.',
            'images.*.max' => 'Each image must not exceed 2MB.',
        ];
    }
}
