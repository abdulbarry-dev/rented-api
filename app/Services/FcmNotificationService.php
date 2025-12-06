<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\User;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Illuminate\Support\Facades\Log;

class FcmNotificationService
{
    private $messaging;

    public function __construct()
    {
        $credentialsPath = storage_path('app/firebase-credentials.json');
        
        if (!file_exists($credentialsPath)) {
            Log::warning('Firebase credentials not found, FCM notifications disabled');
            return;
        }

        try {
            $factory = (new Factory)->withServiceAccount($credentialsPath);
            $this->messaging = $factory->createMessaging();
        } catch (\Exception $e) {
            Log::error('Failed to initialize Firebase Messaging', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send FCM notification to a user.
     * 
     * @param User $user The user to send notification to
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @param string|null $type Notification type for routing
     * @return bool Success status
     */
    public function sendToUser(User $user, string $title, string $body, array $data = [], ?string $type = null): bool
    {
        if (!$this->messaging) {
            Log::warning('FCM Messaging not initialized, skipping notification');
            return false;
        }

        // Get all device tokens for the user
        $deviceTokens = DeviceToken::where('user_id', $user->id)->get();

        if ($deviceTokens->isEmpty()) {
            Log::debug('No device tokens found for user', ['user_id' => $user->id]);
            return false;
        }

        $successCount = 0;
        $failureCount = 0;

        foreach ($deviceTokens as $deviceToken) {
            try {
                $this->sendToToken($deviceToken->token, $title, $body, $data, $type, $deviceToken->device_type);
                $deviceToken->touchLastUsed();
                $successCount++;
            } catch (\Exception $e) {
                Log::error('Failed to send FCM notification to device', [
                    'user_id' => $user->id,
                    'device_token_id' => $deviceToken->id,
                    'error' => $e->getMessage(),
                ]);

                // If token is invalid, delete it
                if (str_contains($e->getMessage(), 'Invalid') || str_contains($e->getMessage(), 'not found')) {
                    $deviceToken->delete();
                    Log::info('Deleted invalid device token', [
                        'device_token_id' => $deviceToken->id,
                    ]);
                }

                $failureCount++;
            }
        }

        Log::info('FCM notification sent', [
            'user_id' => $user->id,
            'success_count' => $successCount,
            'failure_count' => $failureCount,
        ]);

        return $successCount > 0;
    }

    /**
     * Send FCM notification to a specific token.
     * 
     * @param string $token FCM token
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @param string|null $type Notification type
     * @param string|null $deviceType Device type (android, ios)
     * @return void
     */
    public function sendToToken(string $token, string $title, string $body, array $data = [], ?string $type = null, ?string $deviceType = null): void
    {
        if (!$this->messaging) {
            throw new \RuntimeException('FCM Messaging not initialized');
        }

        // Add type to data if provided
        if ($type) {
            $data['type'] = $type;
        }

        // Create notification
        $notification = Notification::create($title, $body);

        // Build message
        $message = CloudMessage::withTarget('token', $token)
            ->withNotification($notification)
            ->withData($data);

        // Add platform-specific configs
        if ($deviceType === 'android') {
            $androidConfig = AndroidConfig::fromArray([
                'priority' => 'high',
                'notification' => [
                    'sound' => 'default',
                    'channel_id' => 'rented_notifications',
                ],
            ]);
            $message = $message->withAndroidConfig($androidConfig);
        } elseif ($deviceType === 'ios') {
            $apnsConfig = ApnsConfig::fromArray([
                'headers' => [
                    'apns-priority' => '10',
                ],
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                        'badge' => 1,
                    ],
                ],
            ]);
            $message = $message->withApnsConfig($apnsConfig);
        }

        // Send message
        $this->messaging->send($message);
    }

    /**
     * Send FCM notification to multiple users.
     * 
     * @param array $userIds Array of user IDs
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @param string|null $type Notification type
     * @return array Results with success and failure counts
     */
    public function sendToUsers(array $userIds, string $title, string $body, array $data = [], ?string $type = null): array
    {
        $results = [
            'success' => 0,
            'failure' => 0,
        ];

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user) {
                if ($this->sendToUser($user, $title, $body, $data, $type)) {
                    $results['success']++;
                } else {
                    $results['failure']++;
                }
            }
        }

        return $results;
    }
}
