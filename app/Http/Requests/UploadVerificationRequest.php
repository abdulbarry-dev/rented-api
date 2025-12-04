<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadVerificationRequest extends FormRequest
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
            'id_front' => 'required|image|mimes:jpeg,jpg,png|max:5120|dimensions:min_width=200,min_height=200',
            'id_back' => 'required|image|mimes:jpeg,jpg,png|max:5120|dimensions:min_width=200,min_height=200',
            'selfie' => 'required|image|mimes:jpeg,jpg,png|max:5120|dimensions:min_width=200,min_height=200',
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
            'id_front.required' => 'Front side of national ID is required.',
            'id_front.image' => 'Front side must be an image file.',
            'id_front.mimes' => 'Front side must be a JPEG, JPG, or PNG file.',
            'id_front.max' => 'Front side must not exceed 5MB.',
            'id_front.dimensions' => 'Front side must be at least 200x200 pixels.',

            'id_back.required' => 'Back side of national ID is required.',
            'id_back.image' => 'Back side must be an image file.',
            'id_back.mimes' => 'Back side must be a JPEG, JPG, or PNG file.',
            'id_back.max' => 'Back side must not exceed 5MB.',
            'id_back.dimensions' => 'Back side must be at least 200x200 pixels.',

            'selfie.required' => 'Selfie photo is required.',
            'selfie.image' => 'Selfie must be an image file.',
            'selfie.mimes' => 'Selfie must be a JPEG, JPG, or PNG file.',
            'selfie.max' => 'Selfie must not exceed 5MB.',
            'selfie.dimensions' => 'Selfie must be at least 200x200 pixels.',
        ];
    }
}
