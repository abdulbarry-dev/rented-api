<?php

namespace App\Repositories;

use App\Models\Dispute;
use Illuminate\Database\Eloquent\Collection;

class DisputeRepository
{
    /**
     * Get disputes by user.
     */
    public function getByUserId(int $userId): Collection
    {
        return Dispute::with(['rental', 'purchase', 'reporter', 'reportedUser'])
            ->where(function ($query) use ($userId) {
                $query->where('reported_by', $userId)
                    ->orWhere('reported_against', $userId);
            })
            ->latest()
            ->get();
    }

    /**
     * Get dispute by ID.
     */
    public function findById(int $id): ?Dispute
    {
        return Dispute::with(['rental', 'purchase', 'reporter', 'reportedUser'])->find($id);
    }

    /**
     * Create a dispute.
     */
    public function create(array $data): Dispute
    {
        return Dispute::create($data);
    }

    /**
     * Update dispute.
     */
    public function update(Dispute $dispute, array $data): bool
    {
        return $dispute->update($data);
    }

    /**
     * Get open disputes.
     */
    public function getOpenDisputes(): Collection
    {
        return Dispute::with(['rental', 'purchase', 'reporter', 'reportedUser'])
            ->whereIn('status', ['open', 'investigating'])
            ->latest()
            ->get();
    }

    /**
     * Resolve dispute.
     */
    public function resolve(Dispute $dispute, string $resolution): bool
    {
        return $dispute->update([
            'status' => 'resolved',
            'resolution' => $resolution,
            'resolved_at' => now(),
        ]);
    }
}
