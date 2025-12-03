<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePurchaseRequest;
use App\Http\Resources\PurchaseResource;
use App\Models\Purchase;
use App\Services\PurchaseService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class PurchaseController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private PurchaseService $service) {}

    public function store(CreatePurchaseRequest $request): JsonResponse
    {
        try {
            $purchase = $this->service->createPurchase(
                $request->validated(),
                $request->user()->id
            );

            return response()->json([
                'message' => 'Purchase request created successfully',
                'data' => new PurchaseResource($purchase->load(['product.user', 'buyer'])),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function complete(int $id): JsonResponse
    {
        $purchase = Purchase::findOrFail($id);

        // Only product owner can complete purchase
        $this->authorize('update', $purchase);

        try {
            $purchase = $this->service->completePurchase($purchase);

            return response()->json([
                'message' => 'Purchase completed successfully',
                'data' => new PurchaseResource($purchase->load(['product.user', 'buyer'])),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function cancel(int $id): JsonResponse
    {
        $purchase = Purchase::findOrFail($id);

        // Only product owner or buyer can cancel purchase
        $this->authorize('update', $purchase);

        try {
            $purchase = $this->service->cancelPurchase($purchase);

            return response()->json([
                'message' => 'Purchase cancelled successfully',
                'data' => new PurchaseResource($purchase->load(['product.user', 'buyer'])),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function userPurchases(): JsonResponse
    {
        $purchases = $this->service->getUserPurchases(auth()->id());

        return response()->json([
            'data' => PurchaseResource::collection($purchases),
        ]);
    }
}
