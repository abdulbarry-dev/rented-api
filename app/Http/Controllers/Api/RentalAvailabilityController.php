<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RentalAvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RentalAvailabilityController extends Controller
{
    public function __construct(
        private RentalAvailabilityService $service
    ) {}

    /**
     * Get availability calendar for a product.
     */
    public function index(int $productId, Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $availability = $this->service->getAvailability(
            $productId,
            $request->start_date,
            $request->end_date
        );

        return response()->json($availability);
    }

    /**
     * Check if product is available for specific dates.
     */
    public function checkAvailability(int $productId, Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $available = $this->service->checkAvailability(
            $productId,
            $request->start_date,
            $request->end_date
        );

        return response()->json([
            'available' => $available,
            'message' => $available
                ? 'Product is available for the selected dates.'
                : 'Product is not available for the selected dates.',
        ]);
    }

    /**
     * Block dates for maintenance (owner only).
     */
    public function blockForMaintenance(int $productId, Request $request): JsonResponse
    {
        $request->validate([
            'dates' => 'required|array',
            'dates.*' => 'date',
            'notes' => 'nullable|string|max:500',
        ]);

        $this->service->blockForMaintenance(
            $productId,
            $request->dates,
            $request->notes
        );

        return response()->json([
            'message' => 'Dates blocked successfully.',
        ]);
    }
}
