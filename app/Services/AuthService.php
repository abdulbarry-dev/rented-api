<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private ImageUploadService $imageUploadService
    ) {}

    /**
     * Register a new user.
     */
    public function register(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    /**
     * Authenticate user and generate token.
     *
     * @throws ValidationException
     */
    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Generate token
        $token = $user->createToken('api-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Revoke all user tokens (logout).
     */
    public function logout(User $user): bool
    {
        return $user->tokens()->delete();
    }

    /**
     * Revoke current access token only.
     */
    public function logoutCurrentDevice(User $user): bool
    {
        return $user->currentAccessToken()->delete();
    }

    /**
     * Update user profile.
     *
     * @throws \Exception
     */
    public function updateProfile(User $user, array $data): User
    {
        // If password is being changed, verify current password
        if (isset($data['password'])) {
            if (! isset($data['current_password'])) {
                throw new \Exception('Current password is required to change password.');
            }

            if (! Hash::check($data['current_password'], $user->password)) {
                throw new \Exception('Current password is incorrect.');
            }

            $data['password'] = Hash::make($data['password']);
        }

        // Remove current_password from data array
        unset($data['current_password'], $data['password_confirmation']);

        // Update user
        $user->update(array_filter($data));

        return $user->fresh();
    }

    /**
     * Update user avatar.
     */
    public function updateAvatar(User $user, UploadedFile|string $avatar): User
    {
        // Delete old avatar if exists
        if ($user->avatar_path) {
            $this->imageUploadService->delete($user->avatar_path);
        }

        // Store new avatar with optimization
        $path = $this->imageUploadService->uploadAvatar($avatar);

        // Update user avatar path
        $user->update([
            'avatar_path' => $path,
        ]);

        return $user->fresh();
    }

    /**
     * Delete user avatar.
     */
    public function deleteAvatar(User $user): User
    {
        if ($user->avatar_path) {
            $this->imageUploadService->delete($user->avatar_path);

            $user->update([
                'avatar_path' => null,
            ]);
        }

        return $user->fresh();
    }
}
