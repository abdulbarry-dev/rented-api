<?php

namespace App\Services;

use App\Models\Dispute;
use App\Models\User;
use App\Repositories\DisputeRepository;
use Illuminate\Database\Eloquent\Collection;

class DisputeService
{
    public function __construct(
        private DisputeRepository $repository
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

        return $this->repository->create($data);
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
        return $this->repository->resolve($dispute, $resolution);
    }

    /**
     * Check if user is involved in dispute.
     */
    public function isUserInvolved(Dispute $dispute, User $user): bool
    {
        return $dispute->reported_by === $user->id || $dispute->reported_against === $user->id;
    }
}
