<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadVerificationRequest;
use App\Http\Resources\VerificationResource;
use App\Services\VerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
}
