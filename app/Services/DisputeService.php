<?php

namespace App\Services;

use App\Models\Dispute;
use App\Models\User;
use App\Repositories\DisputeRepository;
use Illuminate\Database\Eloquent\Collection;

class DisputeService
{
    public function __construct(
        private DisputeRepository $repository,
        private NotificationService $notificationService
    ) {}

    /**
     * Get user's disputes.
     */
    public function getUserDisputes(User $user): Collection
    {
        return $this->repository->getByUserId($user->id);
    }

    /**
     * Get dispute by ID.
     */
    public function getDisputeById(int $id): ?Dispute
    {
        return $this->repository->findById($id);
    }

    /**
     * Create a dispute.
     */
    public function createDispute(User $user, array $data): Dispute
    {
        $data['reported_by'] = $user->id;
        $data['status'] = 'open';

        $dispute = $this->repository->create($data);

        // Load relationships for notifications
        $dispute->load('reportedUser');

        // Create notification for the user being reported against
        if ($dispute->reported_against && $dispute->reportedUser) {
            $disputeType = $dispute->dispute_type ?? 'transaction';
            $this->notificationService->notifyDisputeOpened(
                $dispute->reportedUser,
                $dispute->id,
                $disputeType
            );
        }

        return $dispute;
    }

    /**
     * Update dispute status.
     */
    public function updateDisputeStatus(Dispute $dispute, string $status): bool
    {
        return $this->repository->update($dispute, ['status' => $status]);
    }

    /**
     * Resolve dispute.
     */
    public function resolveDispute(Dispute $dispute, string $resolution): bool
    {
        $result = $this->repository->resolve($dispute, $resolution);

        // Load relationships for notifications
        $dispute->load(['reporter', 'reportedUser']);

        // Create notifications for both parties
        if ($dispute->reporter) {
            $this->notificationService->notifyDisputeResolved(
                $dispute->reporter,
                $dispute->id,
                $resolution
            );
        }

        if ($dispute->reportedUser) {
            $this->notificationService->notifyDisputeResolved(
                $dispute->reportedUser,
                $dispute->id,
                $resolution
            );
        }

        return $result;
    }

    /**
     * Check if user is involved in dispute.
     */
    public function isUserInvolved(Dispute $dispute, User $user): bool
    {
        return $dispute->reported_by === $user->id || $dispute->reported_against === $user->id;
    }
}
