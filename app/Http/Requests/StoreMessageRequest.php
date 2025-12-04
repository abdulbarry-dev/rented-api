<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
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
            'receiver_id' => 'required_without:conversation_id|integer|exists:users,id',
            'conversation_id' => 'required_without:receiver_id|integer|exists:conversations,id',
            'product_id' => 'nullable|integer|exists:products,id',
            'content' => 'required|string|max:5000',
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
            'receiver_id.required_without' => 'Receiver or conversation is required.',
            'receiver_id.exists' => 'Receiver does not exist.',
            'conversation_id.required_without' => 'Conversation or receiver is required.',
            'conversation_id.exists' => 'Conversation does not exist.',
            'product_id.exists' => 'Product does not exist.',
            'content.required' => 'Message content is required.',
            'content.max' => 'Message content must not exceed 5000 characters.',
        ];
    }
}
