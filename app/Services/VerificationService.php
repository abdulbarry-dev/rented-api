<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserVerification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class VerificationService
{
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

        // Store files
        $idFrontPath = $this->storeFile($data['id_front'], 'verifications');
        $idBackPath = $this->storeFile($data['id_back'], 'verifications');

        // Create verification record
        $verification = UserVerification::create([
            'user_id' => $user->id,
            'id_front_path' => $idFrontPath,
            'id_back_path' => $idBackPath,
            'document_type' => $data['document_type'] ?? null,
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
     * Store uploaded file.
     */
    private function storeFile(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'private');
    }

    /**
     * Delete stored files.
     */
    public function deleteVerificationFiles(UserVerification $verification): void
    {
        Storage::disk('private')->delete($verification->id_front_path);
        Storage::disk('private')->delete($verification->id_back_path);
    }
}
