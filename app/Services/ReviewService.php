<?php

namespace App\Services;

use App\Models\Review;
use App\Models\User;
use App\Repositories\ReviewRepository;
use Illuminate\Database\Eloquent\Collection;

class ReviewService
{
    public function __construct(
        private ReviewRepository $repository,
        private NotificationService $notificationService
    ) {}

    /**
     * Get all reviews for a product.
     */
    public function getProductReviews(int $productId): Collection
    {
        return $this->repository->getByProductId($productId);
    }

    /**
     * Get all reviews by a user.
     */
    public function getUserReviews(User $user): Collection
    {
        return $this->repository->getByUserId($user->id);
    }

    /**
     * Get review by ID.
     */
    public function getReviewById(int $id): ?Review
    {
        return $this->repository->findById($id);
    }

    /**
     * Create a new review.
     */
    public function createReview(User $user, array $data): Review
    {
        // Check if user has already reviewed this product
        if ($this->repository->hasUserReviewedProduct($user->id, $data['product_id'])) {
            throw new \Exception('You have already reviewed this product.');
        }

        $data['user_id'] = $user->id;

        $review = $this->repository->create($data);

        // Load relationships for notifications
        $review->load(['product.user']);

        // Create notification for product owner
        if ($review->product->user && $review->product->user->id !== $user->id) {
            $this->notificationService->notifyReviewReceived(
                $review->product->user,
                $review->id,
                $review->product_id,
                $review->product->title,
                $review->rating
            );
        }

        return $review;
    }

    /**
     * Update an existing review.
     */
    public function updateReview(Review $review, array $data): bool
    {
        return $this->repository->update($review, $data);
    }

    /**
     * Delete a review.
     */
    public function deleteReview(Review $review): bool
    {
        return $this->repository->delete($review);
    }

    /**
     * Get product rating statistics.
     */
    public function getProductRatingStats(int $productId): array
    {
        return [
            'average_rating' => $this->repository->getAverageRating($productId),
            'review_count' => $this->repository->getReviewCount($productId),
        ];
    }
}
