<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadVerificationRequest;
use App\Http\Resources\VerificationResource;
use App\Services\VerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class VerificationController extends Controller
{
    public function __construct(
        private VerificationService $verificationService
    ) {}

    /**
     * Upload verification documents.
     */
    public function upload(UploadVerificationRequest $request): JsonResponse
    {
        try {
            $verification = $this->verificationService->uploadDocuments(
                $request->user(),
                $request->validated()
            );

            return response()->json([
                'message' => 'Verification documents uploaded successfully',
                'data' => new VerificationResource($verification),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get user's verification status.
     */
    public function status(Request $request): JsonResponse
    {
        $status = $this->verificationService->getVerificationStatus($request->user());

        return response()->json([
            'data' => $status,
        ]);
    }

    /**
     * Serve verification image securely (owner only).
     */
    public function viewImage(Request $request, string $imageType): Response|JsonResponse
    {
        // Validate image type
        if (! in_array($imageType, ['id_front', 'id_back', 'selfie'])) {
            return response()->json([
                'message' => 'Invalid image type. Must be: id_front, id_back, or selfie.',
            ], 400);
        }

        // Get user's verification
        $verification = $request->user()->verification;

        if (! $verification) {
            return response()->json([
                'message' => 'No verification documents found.',
            ], 404);
        }

        // Authorize access (owner only)
        Gate::authorize('viewImage', $verification);

        // Get image path from JSON field
        $imagePath = match ($imageType) {
            'id_front' => $verification->id_front_path,
            'id_back' => $verification->id_back_path,
            'selfie' => $verification->selfie_path,
        };

        if (! $imagePath || ! Storage::disk('private')->exists($imagePath)) {
            return response()->json([
                'message' => 'Image not found.',
            ], 404);
        }

        // Get file contents
        $file = Storage::disk('private')->get($imagePath);

        // Stream the image securely with security headers
        return response($file, 200, [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
