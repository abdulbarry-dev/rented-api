<?php

namespace App\Services;

use App\Repositories\RentalAvailabilityRepository;
use Illuminate\Support\Carbon;

class RentalAvailabilityService
{
    public function __construct(
        private RentalAvailabilityRepository $repository
    ) {}

    /**
     * Get available dates for a product.
     */
    public function getAvailability(int $productId, ?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate) : now();
        $end = $endDate ? Carbon::parse($endDate) : now()->addMonths(3);

        $blockedDates = $this->repository->getBlockedDates($productId, $start, $end);

        return [
            'product_id' => $productId,
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'blocked_dates' => $blockedDates->map(fn ($item) => [
                'date' => $item->blocked_date->format('Y-m-d'),
                'type' => $item->block_type,
                'notes' => $item->notes,
            ]),
        ];
    }

    /**
     * Check if product is available for rental period.
     */
    public function checkAvailability(int $productId, string $startDate, string $endDate): bool
    {
        return $this->repository->areDatesAvailable(
            $productId,
            Carbon::parse($startDate),
            Carbon::parse($endDate)
        );
    }

    /**
     * Block dates for a rental.
     */
    public function blockForRental(int $productId, string $startDate, string $endDate, int $rentalId): void
    {
        $this->repository->blockDates(
            $productId,
            Carbon::parse($startDate),
            Carbon::parse($endDate),
            $rentalId,
            'booked'
        );
    }

    /**
     * Unblock dates when rental is cancelled.
     */
    public function unblockRental(int $productId, int $rentalId): int
    {
        return $this->repository->unblockDates($productId, $rentalId);
    }

    /**
     * Block dates for maintenance.
     */
    public function blockForMaintenance(int $productId, array $dates, ?string $notes = null): void
    {
        $this->repository->blockForMaintenance($productId, $dates, $notes);
    }
}
