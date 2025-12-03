<?php

namespace App\Repositories;

use App\Models\Purchase;
use Illuminate\Database\Eloquent\Collection;

class PurchaseRepository
{
    public function create(array $data): Purchase
    {
        return Purchase::create($data);
    }

    public function update(Purchase $purchase, array $data): Purchase
    {
        $purchase->update($data);

        return $purchase->fresh();
    }

    public function findById(int $id): ?Purchase
    {
        return Purchase::with(['product', 'buyer'])->find($id);
    }

    public function getUserPurchases(int $userId): Collection
    {
        return Purchase::with(['product.category', 'product.user'])
            ->where('buyer_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getProductPurchases(int $productId): Collection
    {
        return Purchase::with('buyer')
            ->where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
