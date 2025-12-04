<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDisputeRequest extends FormRequest
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
            'rental_id' => 'nullable|required_without:purchase_id|integer|exists:rentals,id',
            'purchase_id' => 'nullable|required_without:rental_id|integer|exists:purchases,id',
            'reported_against' => 'required|integer|exists:users,id',
            'dispute_type' => 'required|in:damage,late_return,not_as_described,payment,other',
            'description' => 'required|string|max:5000',
            'evidence' => 'nullable|array|max:5',
            'evidence.*' => 'string|url',
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
            'rental_id.required_without' => 'Either rental or purchase must be specified.',
            'purchase_id.required_without' => 'Either rental or purchase must be specified.',
            'reported_against.required' => 'User to report against is required.',
            'reported_against.exists' => 'User does not exist.',
            'dispute_type.required' => 'Dispute type is required.',
            'dispute_type.in' => 'Invalid dispute type.',
            'description.required' => 'Description is required.',
            'description.max' => 'Description must not exceed 5000 characters.',
        ];
    }
}
