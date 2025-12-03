<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $service
    ) {
    }

    /**
     * Display a listing of products.
     * Public endpoint - no authentication required.
     */
    public function index(): JsonResponse
    {
        $products = $this->service->getAllProducts();

        return response()->json([
            'data' => ProductResource::collection($products->items()),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * Display the specified product.
     * Public endpoint - no authentication required.
     */
    public function show(int $id): JsonResponse
    {
        $product = $this->service->getProductById($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found',
            ], 404);
        }

        return response()->json([
            'data' => new ProductResource($product),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * Will be implemented in Phase 4.
     */
    public function store()
    {
        // Phase 4 - Product Management
    }

    /**
     * Update the specified resource in storage.
     * Will be implemented in Phase 4.
     */
    public function update()
    {
        // Phase 4 - Product Management
    }

    /**
     * Remove the specified resource from storage.
     * Will be implemented in Phase 4.
     */
    public function destroy()
    {
        // Phase 4 - Product Management
    }
}
