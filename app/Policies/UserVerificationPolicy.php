<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserVerification;

class UserVerificationPolicy
{
    /**
     * Determine if the user can view their verification.
     */
    public function view(User $user, UserVerification $userVerification): bool
    {
        return $user->id === $userVerification->user_id;
    }

    /**
     * Determine if the user can view a specific verification image.
     */
    public function viewImage(User $user, UserVerification $userVerification): bool
    {
        return $user->id === $userVerification->user_id;
    }

    /**
     * Determine if the user can update their verification.
     */
    public function update(User $user, UserVerification $userVerification): bool
    {
        return $user->id === $userVerification->user_id
            && $userVerification->status !== 'verified';
    }

    /**
     * Determine if the user can delete their verification.
     */
    public function delete(User $user, UserVerification $userVerification): bool
    {
        return $user->id === $userVerification->user_id
            && $userVerification->status !== 'verified';
    }
}
