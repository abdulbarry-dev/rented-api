<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Rental;
use App\Repositories\RentalRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class RentalService
{
    public function __construct(private RentalRepository $repository) {}

    public function createRental(array $data, int $renterId): Rental
    {
        $product = Product::findOrFail($data['product_id']);

        // Check if product is available
        if (! $product->is_available) {
            throw new \Exception('Product is not available for rent.');
        }

        // Check if product has rental conflicts
        if (! $this->repository->checkAvailability(
            $product->id,
            $data['start_date'],
            $data['end_date']
        )) {
            throw new \Exception('Product is not available for the selected dates.');
        }

        // Calculate total price
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);
        $days = $startDate->diffInDays($endDate) + 1; // Include both start and end day
        $totalPrice = $days * $product->price_per_day;

        return $this->repository->create([
            'product_id' => $product->id,
            'renter_id' => $renterId,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'total_price' => $totalPrice,
            'status' => Rental::STATUS_PENDING,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function updateRentalStatus(Rental $rental, string $status, ?string $notes = null): Rental
    {
        return $this->repository->update($rental, [
            'status' => $status,
            'notes' => $notes ?? $rental->notes,
        ]);
    }

    public function getUserRentals(int $userId): Collection
    {
        return $this->repository->getUserRentals($userId);
    }

    public function getProductRentals(int $productId): Collection
    {
        return $this->repository->getProductRentals($productId);
    }

    public function getRentalById(int $id): ?Rental
    {
        return $this->repository->findById($id);
    }
}
