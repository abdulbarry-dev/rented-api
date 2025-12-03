<?php

namespace App\Repositories;

use App\Models\Rental;
use Illuminate\Database\Eloquent\Collection;

class RentalRepository
{
    public function create(array $data): Rental
    {
        return Rental::create($data);
    }

    public function update(Rental $rental, array $data): Rental
    {
        $rental->update($data);

        return $rental->fresh();
    }

    public function findById(int $id): ?Rental
    {
        return Rental::with(['product', 'renter'])->find($id);
    }

    public function getUserRentals(int $userId): Collection
    {
        return Rental::with(['product.category', 'product.user'])
            ->where('renter_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getProductRentals(int $productId): Collection
    {
        return Rental::with('renter')
            ->where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function checkAvailability(int $productId, string $startDate, string $endDate): bool
    {
        return ! Rental::where('product_id', $productId)
            ->whereIn('status', [Rental::STATUS_APPROVED, Rental::STATUS_ACTIVE])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->exists();
    }
}
