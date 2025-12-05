<?php

namespace App\Services;

use App\Events\RentalCreated;
use App\Events\RentalStatusChanged;
use App\Models\Product;
use App\Models\Rental;
use App\Repositories\RentalRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class RentalService
{
    public function __construct(
        private RentalRepository $repository,
        private NotificationService $notificationService
    ) {}

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

        $rental = $this->repository->create([
            'product_id' => $product->id,
            'renter_id' => $renterId,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'total_price' => $totalPrice,
            'status' => Rental::STATUS_PENDING,
            'notes' => $data['notes'] ?? null,
        ]);

        // Load relationships for broadcasting and notifications
        $rental->load(['product.user', 'renter']);

        // Create notification for product owner
        $this->notificationService->notifyRentalRequested(
            $rental->product->user,
            $rental->id,
            $rental->product_id,
            $rental->product->title,
            $rental->renter->name
        );

        // Broadcast rental created event
        broadcast(new RentalCreated($rental));

        return $rental;
    }

    public function updateRentalStatus(Rental $rental, string $status, ?string $notes = null): Rental
    {
        $oldStatus = $rental->status;
        
        $rental = $this->repository->update($rental, [
            'status' => $status,
            'notes' => $notes ?? $rental->notes,
        ]);

        // Load relationships for broadcasting and notifications
        $rental->load(['product.user', 'renter']);

        // Broadcast status changed event if status actually changed
        if ($oldStatus !== $status) {
            // Create notifications based on status change
            if ($status === Rental::STATUS_APPROVED) {
                // Notify renter that rental is confirmed
                $this->notificationService->notifyRentalConfirmed(
                    $rental->renter,
                    $rental->id,
                    $rental->product_id,
                    $rental->product->title
                );
            } elseif ($status === Rental::STATUS_COMPLETED) {
                // Notify both parties that rental is completed
                $this->notificationService->notifyRentalCompleted(
                    $rental->renter,
                    $rental->id,
                    $rental->product_id,
                    $rental->product->title
                );
                $this->notificationService->notifyRentalCompleted(
                    $rental->product->user,
                    $rental->id,
                    $rental->product_id,
                    $rental->product->title
                );
            }

            broadcast(new RentalStatusChanged($rental, $oldStatus));
        }

        return $rental;
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
