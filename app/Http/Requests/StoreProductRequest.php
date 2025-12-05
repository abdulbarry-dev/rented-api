<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'category_id' => 'required|integer|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'price_per_day' => 'required|numeric|min:1|max:999999.99',
            'is_for_sale' => 'nullable|boolean',
            'sale_price' => 'nullable|numeric|min:1|max:999999.99|required_if:is_for_sale,true',
            'is_available' => 'nullable|boolean',
            // Accept either file upload or path string (for pre-uploaded images)
            'thumbnail' => 'nullable',
            'images' => 'nullable|array|max:10',
            'images.*' => 'nullable',
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
            'category_id.required' => 'Category is required.',
            'category_id.exists' => 'Selected category does not exist.',
            'title.required' => 'Product title is required.',
            'title.max' => 'Product title must not exceed 255 characters.',
            'description.required' => 'Product description is required.',
            'description.max' => 'Description must not exceed 5000 characters.',
            'price_per_day.required' => 'Daily rental price is required.',
            'price_per_day.min' => 'Daily rental price must be at least 1.',
            'price_per_day.numeric' => 'Daily rental price must be a number.',
            'sale_price.required_if' => 'Sale price is required when product is for sale.',
            'sale_price.min' => 'Sale price must be at least 1.',
            'images.max' => 'You can upload a maximum of 10 images.',
        ];
    }
}
