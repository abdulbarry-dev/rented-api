<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class FcmController extends Controller
{
    /**
     * Register or update FCM token for the authenticated user.
     */
    public function updateToken(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'token' => 'required|string',
                'device_type' => 'nullable|string|in:android,ios,web',
                'device_id' => 'nullable|string|max:255',
                'app_version' => 'nullable|string|max:50',
            ]);

            $user = $request->user();

            // Check if token already exists for this user
            $deviceToken = DeviceToken::where('user_id', $user->id)
                ->where('token', $validated['token'])
                ->first();

            if ($deviceToken) {
                // Update existing token
                $deviceToken->update([
                    'device_type' => $validated['device_type'] ?? $deviceToken->device_type,
                    'device_id' => $validated['device_id'] ?? $deviceToken->device_id,
                    'app_version' => $validated['app_version'] ?? $deviceToken->app_version,
                    'last_used_at' => now(),
                ]);

                Log::info('FCM token updated', [
                    'user_id' => $user->id,
                    'token' => substr($validated['token'], 0, 20) . '...',
                ]);

                return response()->json([
                    'message' => 'FCM token updated successfully',
                    'device_token_id' => $deviceToken->id,
                ]);
            }

            // Check if token exists for another user (shouldn't happen, but handle it)
            $existingToken = DeviceToken::where('token', $validated['token'])->first();
            if ($existingToken) {
                // Delete old token and create new one
                $existingToken->delete();
                Log::warning('FCM token reassigned to different user', [
                    'old_user_id' => $existingToken->user_id,
                    'new_user_id' => $user->id,
                ]);
            }

            // Create new device token
            $deviceToken = DeviceToken::create([
                'user_id' => $user->id,
                'token' => $validated['token'],
                'device_type' => $validated['device_type'] ?? 'android',
                'device_id' => $validated['device_id'],
                'app_version' => $validated['app_version'],
                'last_used_at' => now(),
            ]);

            Log::info('FCM token registered', [
                'user_id' => $user->id,
                'device_token_id' => $deviceToken->id,
                'device_type' => $deviceToken->device_type,
            ]);

            return response()->json([
                'message' => 'FCM token registered successfully',
                'device_token_id' => $deviceToken->id,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update FCM token', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'message' => 'Failed to update FCM token',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete FCM token (on logout or app uninstall).
     */
    public function deleteToken(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'token' => 'required|string',
            ]);

            $user = $request->user();

            $deviceToken = DeviceToken::where('user_id', $user->id)
                ->where('token', $validated['token'])
                ->first();

            if ($deviceToken) {
                $deviceToken->delete();

                Log::info('FCM token deleted', [
                    'user_id' => $user->id,
                    'device_token_id' => $deviceToken->id,
                ]);

                return response()->json([
                    'message' => 'FCM token deleted successfully',
                ]);
            }

            return response()->json([
                'message' => 'FCM token not found',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to delete FCM token', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'message' => 'Failed to delete FCM token',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all device tokens for the authenticated user.
     */
    public function getTokens(Request $request): JsonResponse
    {
        $user = $request->user();
        $tokens = DeviceToken::where('user_id', $user->id)
            ->select('id', 'device_type', 'device_id', 'app_version', 'last_used_at', 'created_at')
            ->get();

        return response()->json([
            'tokens' => $tokens,
        ]);
    }
}
