<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SocialAuthService;
use Illuminate\Http\JsonResponse;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function __construct(
        private SocialAuthService $service
    ) {}

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
}
