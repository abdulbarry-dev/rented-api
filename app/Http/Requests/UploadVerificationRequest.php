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
            'id_front' => 'required|image|mimes:jpeg,jpg,png,pdf|max:5120',
            'id_back' => 'required|image|mimes:jpeg,jpg,png,pdf|max:5120',
            'document_type' => 'nullable|string|in:passport,nid,driver_license',
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
            'id_front.required' => 'Front side of ID document is required.',
            'id_front.image' => 'Front side must be an image file.',
            'id_front.mimes' => 'Front side must be a JPEG, JPG, PNG, or PDF file.',
            'id_front.max' => 'Front side must not exceed 5MB.',
            'id_back.required' => 'Back side of ID document is required.',
            'id_back.image' => 'Back side must be an image file.',
            'id_back.mimes' => 'Back side must be a JPEG, JPG, PNG, or PDF file.',
            'id_back.max' => 'Back side must not exceed 5MB.',
            'document_type.in' => 'Document type must be passport, nid, or driver_license.',
        ];
    }
}
