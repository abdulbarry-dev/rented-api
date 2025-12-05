<?php

namespace App\Http\Requests;

use App\Models\RentalAvailability;
use Illuminate\Foundation\Http\FormRequest;

class StoreOfferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled in controller/policy
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'offer_type' => 'required|in:rental,purchase',
            'amount' => 'required|numeric|min:0.01',
            'start_date' => 'required_if:offer_type,rental|nullable|date|after_or_equal:today',
            'end_date' => 'required_if:offer_type,rental|nullable|date|after:start_date',
            'message' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Product is required.',
            'product_id.exists' => 'Product does not exist.',
            'offer_type.required' => 'Offer type is required.',
            'offer_type.in' => 'Offer type must be either rental or purchase.',
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a number.',
            'amount.min' => 'Amount must be at least $0.01.',
            'start_date.required_if' => 'Start date is required for rental offers.',
            'start_date.after_or_equal' => 'Start date must be today or later.',
            'end_date.required_if' => 'End date is required for rental offers.',
            'end_date.after' => 'End date must be after start date.',
            'message.max' => 'Message cannot exceed 1000 characters.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Check if user is a conversation participant
            $conversation = $this->route('conversation');
            $user = auth()->user();

            if ($conversation && ! $this->isConversationParticipant($conversation, $user)) {
                $validator->errors()->add('conversation', 'You are not a participant in this conversation.');
            }

            // Check if product is available
            if ($this->product_id) {
                $product = \App\Models\Product::find($this->product_id);
                if ($product && ! $product->is_available) {
                    $validator->errors()->add('product_id', 'Product is not available.');
                }
            }

            // For rental offers, check date availability
            if ($this->offer_type === 'rental' && $this->start_date && $this->end_date && $this->product_id) {
                if (! $this->areDatesAvailable()) {
                    $validator->errors()->add('dates', 'Product is not available for the selected dates.');
                }
            }
        });
    }

    /**
     * Check if user is a conversation participant.
     */
    protected function isConversationParticipant($conversation, $user): bool
    {
        return $conversation->user_one_id === $user->id || $conversation->user_two_id === $user->id;
    }

    /**
     * Check if dates are available for rental.
     */
    protected function areDatesAvailable(): bool
    {
        $blockedDates = RentalAvailability::where('product_id', $this->product_id)
            ->whereBetween('blocked_date', [$this->start_date, $this->end_date])
            ->exists();

        return ! $blockedDates;
    }
}
