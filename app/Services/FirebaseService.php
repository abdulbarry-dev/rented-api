<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    private Auth $auth;

    public function __construct()
    {
        $credentialsPath = storage_path('app/firebase-credentials.json');
        
        if (!file_exists($credentialsPath)) {
            throw new \RuntimeException('Firebase credentials file not found at: ' . $credentialsPath);
        }

        $factory = (new Factory)->withServiceAccount($credentialsPath);
        $this->auth = $factory->createAuth();
    }

    /**
     * Verify Firebase ID token and return decoded token
     * 
     * @param string $idToken Firebase ID token from client
     * @return array Decoded token with user information
     * @throws \Exception If token is invalid
     */
    public function verifyIdToken(string $idToken): array
    {
        try {
            $verifiedToken = $this->auth->verifyIdToken($idToken);
            
            return [
                'uid' => $verifiedToken->claims()->get('sub'),
                'email' => $verifiedToken->claims()->get('email'),
                'name' => $verifiedToken->claims()->get('name'),
                'picture' => $verifiedToken->claims()->get('picture'),
                'email_verified' => $verifiedToken->claims()->get('email_verified', false),
            ];
        } catch (FailedToVerifyToken $e) {
            Log::error('Firebase token verification failed', [
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Invalid Firebase token: ' . $e->getMessage(), 401);
        } catch (\Exception $e) {
            Log::error('Firebase service error', [
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Firebase verification error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get user information from Firebase UID
     * 
     * @param string $uid Firebase user UID
     * @return array User information
     */
    public function getUser(string $uid): array
    {
        try {
            $user = $this->auth->getUser($uid);
            
            return [
                'uid' => $user->uid,
                'email' => $user->email,
                'displayName' => $user->displayName,
                'photoUrl' => $user->photoUrl,
                'emailVerified' => $user->emailVerified,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get Firebase user', [
                'uid' => $uid,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Failed to get Firebase user: ' . $e->getMessage(), 500);
        }
    }
}
