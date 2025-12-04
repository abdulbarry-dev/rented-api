<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FavouriteResource;
use App\Services\FavouriteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FavouriteController extends Controller
{
    public function __construct(
        private FavouriteService $service
    ) {}

    /**
     * Get all favourites for the authenticated user.
     */
    public function index(): AnonymousResourceCollection
    {
        $favourites = $this->service->getUserFavourites(auth()->user());

        return FavouriteResource::collection($favourites);
    }

    /**
     * Toggle favourite status for a product.
     */
    public function toggle(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $result = $this->service->toggleFavourite(
            auth()->user(),
            $request->product_id
        );

        return response()->json($result);
    }

    /**
     * Check if product is favourited.
     */
    public function check(int $productId): JsonResponse
    {
        $favourited = $this->service->hasFavourited(auth()->user(), $productId);

        return response()->json([
            'favourited' => $favourited,
        ]);
    }

    /**
     * Remove a product from favourites.
     */
    public function destroy(int $productId): JsonResponse
    {
        try {
            $this->service->removeFavourite(auth()->user(), $productId);

            return response()->json([
                'message' => 'Product removed from favourites.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
