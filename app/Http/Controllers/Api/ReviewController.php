<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReviewController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public function __construct(
        private ReviewService $service
    ) {}

    /**
     * Get all reviews for a product.
     */
    public function productReviews(int $productId): AnonymousResourceCollection
    {
        $reviews = $this->service->getProductReviews($productId);

        return ReviewResource::collection($reviews);
    }

    /**
     * Get all reviews by the authenticated user.
     */
    public function userReviews(): AnonymousResourceCollection
    {
        $reviews = $this->service->getUserReviews(auth()->user());

        return ReviewResource::collection($reviews);
    }

    /**
     * Get product rating statistics.
     */
    public function productRatingStats(int $productId): JsonResponse
    {
        $stats = $this->service->getProductRatingStats($productId);

        return response()->json($stats);
    }

    /**
     * Store a new review.
     */
    public function store(StoreReviewRequest $request): ReviewResource|JsonResponse
    {
        try {
            $review = $this->service->createReview(auth()->user(), $request->validated());

            return new ReviewResource($review);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update an existing review.
     */
    public function update(UpdateReviewRequest $request, int $id): ReviewResource|JsonResponse
    {
        $review = $this->service->getReviewById($id);

        if (! $review) {
            return response()->json([
                'message' => 'Review not found.',
            ], 404);
        }

        $this->authorize('update', $review);

        $this->service->updateReview($review, $request->validated());

        return new ReviewResource($review->fresh());
    }

    /**
     * Delete a review.
     */
    public function destroy(int $id): JsonResponse
    {
        $review = $this->service->getReviewById($id);

        if (! $review) {
            return response()->json([
                'message' => 'Review not found.',
            ], 404);
        }

        $this->authorize('delete', $review);

        $this->service->deleteReview($review);

        return response()->json([
            'message' => 'Review deleted successfully.',
        ]);
    }
}
