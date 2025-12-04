<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class SocialAuthService
{
    /**
     * Handle OAuth user from provider.
     */
    public function handleProviderCallback(SocialiteUser $socialUser, string $provider): array
    {
        // Find or create user
        $user = User::where('google_id', $socialUser->getId())
            ->orWhere('email', $socialUser->getEmail())
            ->first();

        if (! $user) {
            $user = $this->createUser($socialUser, $provider);
        } else {
            $this->updateUser($user, $socialUser, $provider);
        }

        // Generate token
        $token = $user->createToken('google-auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Create new user from social provider.
     */
    private function createUser(SocialiteUser $socialUser, string $provider): User
    {
        return User::create([
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
            'google_id' => $socialUser->getId(),
            'provider' => $provider,
            'google_token' => $socialUser->token,
            'google_refresh_token' => $socialUser->refreshToken ?? null,
            'avatar_path' => $this->saveAvatar($socialUser->getAvatar()),
            'password' => Hash::make(Str::random(32)), // Random password for OAuth users
            'email_verified_at' => now(), // OAuth users are considered verified
        ]);
    }

    /**
     * Update existing user with OAuth data.
     */
    private function updateUser(User $user, SocialiteUser $socialUser, string $provider): void
    {
        $user->update([
            'google_id' => $socialUser->getId(),
            'provider' => $provider,
            'google_token' => $socialUser->token,
            'google_refresh_token' => $socialUser->refreshToken ?? null,
        ]);

        // Update avatar if not set
        if (! $user->avatar_path && $socialUser->getAvatar()) {
            $user->update([
                'avatar_path' => $this->saveAvatar($socialUser->getAvatar()),
            ]);
        }
    }

    /**
     * Save avatar URL (you can enhance this to download and store locally).
     */
    private function saveAvatar(?string $avatarUrl): ?string
    {
        // For now, just store the URL. You can enhance this to download and store locally
        return $avatarUrl;
    }
}
