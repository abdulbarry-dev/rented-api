<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Repositories\PurchaseRepository;
use Illuminate\Database\Eloquent\Collection;

class PurchaseService
{
    public function __construct(private PurchaseRepository $repository) {}

    public function createPurchase(array $data, int $buyerId): Purchase
    {
        $product = Product::findOrFail($data['product_id']);

        // Check if product is for sale
        if (! $product->is_for_sale) {
            throw new \Exception('Product is not available for purchase.');
        }

        // Check if product is available
        if (! $product->is_available) {
            throw new \Exception('Product is no longer available.');
        }

        // Check if there's already a completed purchase for this product
        $existingPurchase = Purchase::where('product_id', $product->id)
            ->where('status', Purchase::STATUS_COMPLETED)
            ->exists();

        if ($existingPurchase) {
            throw new \Exception('Product has already been sold.');
        }

        // Create purchase
        $purchase = $this->repository->create([
            'product_id' => $product->id,
            'buyer_id' => $buyerId,
            'purchase_price' => $product->sale_price,
            'status' => Purchase::STATUS_PENDING,
            'notes' => $data['notes'] ?? null,
        ]);

        // Mark product as unavailable
        $product->update(['is_available' => false]);

        return $purchase;
    }

    public function completePurchase(Purchase $purchase): Purchase
    {
        return $this->repository->update($purchase, [
            'status' => Purchase::STATUS_COMPLETED,
        ]);
    }

    public function cancelPurchase(Purchase $purchase): Purchase
    {
        // Make product available again
        $purchase->product->update(['is_available' => true]);

        return $this->repository->update($purchase, [
            'status' => Purchase::STATUS_CANCELLED,
        ]);
    }

    public function getUserPurchases(int $userId): Collection
    {
        return $this->repository->getUserPurchases($userId);
    }

    public function getPurchaseById(int $id): ?Purchase
    {
        return $this->repository->findById($id);
    }
}
