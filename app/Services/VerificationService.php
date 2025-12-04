<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserVerification;

class VerificationService
{
    public function __construct(
        private ImageOptimizationService $imageOptimizer
    ) {}

    /**
     * Upload verification documents.
     */
    public function uploadDocuments(User $user, array $data): UserVerification
    {
        // Check if user already has a pending or verified submission
        $existingVerification = $user->verification()
            ->whereIn('status', ['pending', 'verified'])
            ->first();

        if ($existingVerification) {
            if ($existingVerification->status === 'verified') {
                throw new \Exception('User is already verified.');
            }

            if ($existingVerification->status === 'pending') {
                throw new \Exception('A verification request is already pending review.');
            }
        }

        // Optimize and store images
        $idFrontPath = $this->imageOptimizer->optimizeAndStore(
            $data['id_front'],
            'verifications/national-ids',
            1920,
            85
        );

        $idBackPath = $this->imageOptimizer->optimizeAndStore(
            $data['id_back'],
            'verifications/national-ids',
            1920,
            85
        );

        $selfiePath = $this->imageOptimizer->optimizeAndStore(
            $data['selfie'],
            'verifications/selfies',
            1920,
            85
        );

        // Create verification record with JSON structure
        $verification = UserVerification::create([
            'user_id' => $user->id,
            'verification_images' => [
                'id_front' => $idFrontPath,
                'id_back' => $idBackPath,
                'selfie' => $selfiePath,
            ],
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        return $verification;
    }

    /**
     * Get user's verification status.
     */
    public function getVerificationStatus(User $user): array
    {
        $verification = $user->verification;

        return [
            'status' => $user->verification_status,
            'verified_at' => $user->verified_at,
            'rejection_reason' => $user->rejection_reason,
            'has_submitted_documents' => $verification !== null,
            'document_status' => $verification?->status,
            'submitted_at' => $verification?->submitted_at,
            'has_images' => $verification ? [
                'id_front' => $verification->hasIdFront(),
                'id_back' => $verification->hasIdBack(),
                'selfie' => $verification->hasSelfie(),
            ] : null,
        ];
    }

    /**
     * Approve verification (Admin function - for future use).
     */
    public function approveVerification(UserVerification $verification): bool
    {
        $verification->update([
            'status' => 'verified',
            'reviewed_at' => now(),
        ]);

        $verification->user->update([
            'verification_status' => 'verified',
            'verified_at' => now(),
            'rejection_reason' => null,
        ]);

        return true;
    }

    /**
     * Reject verification (Admin function - for future use).
     */
    public function rejectVerification(UserVerification $verification, string $reason): bool
    {
        $verification->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'admin_notes' => $reason,
        ]);

        $verification->user->update([
            'verification_status' => 'rejected',
            'rejection_reason' => $reason,
            'verified_at' => null,
        ]);

        return true;
    }

    /**
     * Delete verification files.
     */
    public function deleteVerificationFiles(UserVerification $verification): void
    {
        if ($verification->verification_images) {
            $this->imageOptimizer->deleteMultiple([
                $verification->id_front_path,
                $verification->id_back_path,
                $verification->selfie_path,
            ]);
        }
    }
}
