<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRentalRequest;
use App\Http\Requests\UpdateRentalRequest;
use App\Http\Resources\RentalResource;
use App\Models\Rental;
use App\Services\RentalService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class RentalController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private RentalService $service) {}

    public function store(CreateRentalRequest $request): JsonResponse
    {
        try {
            $rental = $this->service->createRental(
                $request->validated(),
                $request->user()->id
            );

            return response()->json([
                'message' => 'Rental request created successfully',
                'data' => new RentalResource($rental->load(['product.user', 'renter'])),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function update(UpdateRentalRequest $request, int $id): JsonResponse
    {
        $rental = Rental::findOrFail($id);

        // Only product owner can update rental status
        $this->authorize('update', $rental);

        try {
            $rental = $this->service->updateRentalStatus(
                $rental,
                $request->status,
                $request->notes
            );

            return response()->json([
                'message' => 'Rental status updated successfully',
                'data' => new RentalResource($rental->load(['product.user', 'renter'])),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function userRentals(): JsonResponse
    {
        $rentals = $this->service->getUserRentals(auth()->id());

        return response()->json([
            'data' => RentalResource::collection($rentals),
        ]);
    }

    public function productRentals(int $productId): JsonResponse
    {
        $rentals = $this->service->getProductRentals($productId);

        return response()->json([
            'data' => RentalResource::collection($rentals),
        ]);
    }
}
