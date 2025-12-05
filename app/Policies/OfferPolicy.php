<?php

namespace App\Policies;

use App\Models\Offer;
use App\Models\User;

class OfferPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Any authenticated user can view offers in their conversations
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Offer $offer): bool
    {
        // User must be either sender or receiver
        return $user->id === $offer->sender_id || $user->id === $offer->receiver_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Conversation participant check is in the request validator
    }

    /**
     * Determine whether the user can accept the offer.
     */
    public function accept(User $user, Offer $offer): bool
    {
        // Only the receiver can accept the offer
        return $user->id === $offer->receiver_id && $offer->canBeResponded();
    }

    /**
     * Determine whether the user can reject the offer.
     */
    public function reject(User $user, Offer $offer): bool
    {
        // Only the receiver can reject the offer
        return $user->id === $offer->receiver_id && $offer->canBeResponded();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Offer $offer): bool
    {
        // Only sender can delete their own pending offers
        return $user->id === $offer->sender_id && $offer->isPending();
    }
}
