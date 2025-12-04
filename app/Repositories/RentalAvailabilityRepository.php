<?php

namespace App\Repositories;

use App\Models\RentalAvailability;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class RentalAvailabilityRepository
{
    /**
     * Get blocked dates for a product.
     */
    public function getBlockedDates(int $productId, ?Carbon $startDate = null, ?Carbon $endDate = null): Collection
    {
        $query = RentalAvailability::where('product_id', $productId);

        if ($startDate) {
            $query->where('blocked_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('blocked_date', '<=', $endDate);
        }

        return $query->orderBy('blocked_date')->get();
    }

    /**
     * Check if dates are available.
     */
    public function areDatesAvailable(int $productId, Carbon $startDate, Carbon $endDate): bool
    {
        return ! RentalAvailability::where('product_id', $productId)
            ->whereBetween('blocked_date', [$startDate, $endDate])
            ->exists();
    }

    /**
     * Block dates for a rental.
     */
    public function blockDates(int $productId, Carbon $startDate, Carbon $endDate, int $rentalId, string $blockType = 'booked'): void
    {
        $dates = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $dates[] = [
                'product_id' => $productId,
                'blocked_date' => $currentDate->format('Y-m-d'),
                'block_type' => $blockType,
                'rental_id' => $rentalId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $currentDate->addDay();
        }

        RentalAvailability::insert($dates);
    }

    /**
     * Unblock dates for a rental.
     */
    public function unblockDates(int $productId, int $rentalId): int
    {
        return RentalAvailability::where('product_id', $productId)
            ->where('rental_id', $rentalId)
            ->delete();
    }

    /**
     * Block specific dates for maintenance.
     */
    public function blockForMaintenance(int $productId, array $dates, ?string $notes = null): void
    {
        $data = [];
        foreach ($dates as $date) {
            $data[] = [
                'product_id' => $productId,
                'blocked_date' => $date,
                'block_type' => 'maintenance',
                'notes' => $notes,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        RentalAvailability::insert($data);
    }
}
