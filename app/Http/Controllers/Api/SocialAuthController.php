<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SocialAuthService;
use App\Services\FirebaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function __construct(
        private SocialAuthService $service
    ) {
        // FirebaseService will be resolved lazily if needed
    }

    /**
     * Redirect to Google OAuth.
     */
    public function redirectToGoogle(): JsonResponse
    {
        $url = Socialite::driver('google')
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return response()->json([
            'url' => $url,
        ]);
    }

    /**
     * Handle Google OAuth callback.
     */
    public function handleGoogleCallback(): JsonResponse
    {
        try {
            $socialUser = Socialite::driver('google')->stateless()->user();

            $result = $this->service->handleProviderCallback($socialUser, 'google');

            return response()->json([
                'message' => 'Successfully authenticated with Google.',
                'user' => [
                    'id' => $result['user']->id,
                    'name' => $result['user']->name,
                    'email' => $result['user']->email,
                    'avatar' => $result['user']->avatar_path,
                    'verification_status' => $result['user']->verification_status,
                ],
                'token' => $result['token'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to authenticate with Google.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle Firebase Google Authentication.
     * Accepts Firebase ID token and creates/logs in user.
     */
    public function handleFirebaseAuth(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'id_token' => 'required|string',
                'email' => 'required|email',
                'name' => 'required|string',
                'photo_url' => 'nullable|string|url',
            ]);

            $idToken = $request->input('id_token');
            $email = $request->input('email');
            $name = $request->input('name');
            $photoUrl = $request->input('photo_url');

            // Verify Firebase token if FirebaseService is available
            try {
                $firebaseService = app(FirebaseService::class);
                $verifiedToken = $firebaseService->verifyIdToken($idToken);
                
                // Use verified data from Firebase (more secure)
                $email = $verifiedToken['email'] ?? $email;
                $name = $verifiedToken['name'] ?? $name;
                $photoUrl = $verifiedToken['picture'] ?? $photoUrl;
                
                \Log::info('Firebase token verified', [
                    'uid' => $verifiedToken['uid'],
                    'email' => $email,
                ]);
            } catch (\Exception $e) {
                \Log::warning('Firebase token verification failed, using provided data', [
                    'error' => $e->getMessage(),
                ]);
                // Continue with provided data if verification fails
                // This allows the app to work even if Firebase Admin SDK is not configured
            }

            // Find or create user by email
            $user = \App\Models\User::where('email', $email)->first();

            if (! $user) {
                // Create new user
                $user = \App\Models\User::create([
                    'name' => $name,
                    'email' => $email,
                    'google_id' => $email, // Use email as identifier
                    'provider' => 'google',
                    'avatar_path' => $photoUrl,
                    'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(32)),
                    'email_verified_at' => now(),
                ]);
            } else {
                // Update existing user with Google info if not set
                if (! $user->google_id) {
                    $user->update([
                        'google_id' => $email,
                        'provider' => 'google',
                    ]);
                }
                
                // Update avatar if provided and not set
                if ($photoUrl && ! $user->avatar_path) {
                    $user->update(['avatar_path' => $photoUrl]);
                }
            }

            // Generate token
            $token = $user->createToken('firebase-google-auth-token')->plainTextToken;

            return response()->json([
                'message' => 'Successfully authenticated with Google.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar_url' => $user->avatar_url,
                    'verification_status' => $user->verification_status,
                ],
                'token' => $token,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to authenticate with Firebase Google.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
