<?php

namespace App\Repositories;

use App\Models\Favourite;
use Illuminate\Database\Eloquent\Collection;

class FavouriteRepository
{
    /**
     * Get all favourites by a user.
     */
    public function getByUserId(int $userId): Collection
    {
        return Favourite::with(['product', 'product.category', 'product.user'])
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    /**
     * Check if user has favourited a product.
     */
    public function hasFavourited(int $userId, int $productId): bool
    {
        return Favourite::where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * Find favourite by user and product.
     */
    public function findByUserAndProduct(int $userId, int $productId): ?Favourite
    {
        return Favourite::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();
    }

    /**
     * Create a new favourite.
     */
    public function create(array $data): Favourite
    {
        return Favourite::create($data);
    }

    /**
     * Delete a favourite.
     */
    public function delete(Favourite $favourite): bool
    {
        return $favourite->delete();
    }

    /**
     * Get favourite count for a product.
     */
    public function getFavouriteCount(int $productId): int
    {
        return Favourite::where('product_id', $productId)->count();
    }
}
