<?php

namespace App\Repositories;

use App\Models\Review;
use Illuminate\Database\Eloquent\Collection;

class ReviewRepository
{
    /**
     * Get all reviews for a product.
     */
    public function getByProductId(int $productId): Collection
    {
        return Review::with('user')
            ->where('product_id', $productId)
            ->latest()
            ->get();
    }

    /**
     * Get all reviews by a user.
     */
    public function getByUserId(int $userId): Collection
    {
        return Review::with(['product', 'product.category'])
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    /**
     * Find review by ID.
     */
    public function findById(int $id): ?Review
    {
        return Review::with(['user', 'product'])->find($id);
    }

    /**
     * Check if user has reviewed a product.
     */
    public function hasUserReviewedProduct(int $userId, int $productId): bool
    {
        return Review::where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * Create a new review.
     */
    public function create(array $data): Review
    {
        return Review::create($data);
    }

    /**
     * Update an existing review.
     */
    public function update(Review $review, array $data): bool
    {
        return $review->update($data);
    }

    /**
     * Delete a review.
     */
    public function delete(Review $review): bool
    {
        return $review->delete();
    }

    /**
     * Get average rating for a product.
     */
    public function getAverageRating(int $productId): float
    {
        return (float) Review::where('product_id', $productId)
            ->avg('rating') ?? 0;
    }

    /**
     * Get review count for a product.
     */
    public function getReviewCount(int $productId): int
    {
        return Review::where('product_id', $productId)->count();
    }
}
