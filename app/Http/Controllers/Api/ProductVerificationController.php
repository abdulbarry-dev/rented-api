<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductVerificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}
    /**
     * Get all pending products for verification.
     */
    public function pending(): JsonResponse
    {
        $products = Product::with(['user', 'category'])
            ->where('verification_status', 'pending')
            ->latest()
            ->paginate(20);

        return response()->json($products);
    }

    /**
     * Approve a product.
     */
    public function approve(Product $product): JsonResponse
    {
        $product->update([
            'verification_status' => 'approved',
            'verified_at' => now(),
            'rejection_reason' => null,
        ]);

        // Load user relationship for notification
        $product->load('user');

        // Create notification for product owner
        if ($product->user) {
            $this->notificationService->notifyProductApproved(
                $product->user,
                $product->id,
                $product->title
            );
        }

        return response()->json([
            'message' => 'Product approved successfully',
            'product' => $product->fresh(),
        ]);
    }

    /**
     * Reject a product.
     */
    public function reject(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $product->update([
            'verification_status' => 'rejected',
            'rejection_reason' => $request->reason,
            'verified_at' => now(),
        ]);

        // Load user relationship for notification
        $product->load('user');

        // Create notification for product owner
        if ($product->user) {
            $this->notificationService->notifyProductRejected(
                $product->user,
                $product->id,
                $product->title,
                $request->reason
            );
        }

        return response()->json([
            'message' => 'Product rejected successfully',
            'product' => $product->fresh(),
        ]);
    }

    /**
     * Get all rejected products.
     */
    public function rejected(): JsonResponse
    {
        $products = Product::with(['user', 'category'])
            ->where('verification_status', 'rejected')
            ->latest('verified_at')
            ->paginate(20);

        return response()->json($products);
    }

    /**
     * Get all approved products.
     */
    public function approved(): JsonResponse
    {
        $products = Product::with(['user', 'category'])
            ->where('verification_status', 'approved')
            ->latest('verified_at')
            ->paginate(20);

        return response()->json($products);
    }
}
