<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private ProductService $service
    ) {}

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

        if (! $product) {
            return response()->json([
                'message' => 'Product not found',
            ], 404);
        }

        return response()->json([
            'data' => new ProductResource($product),
        ]);
    }

    /**
     * Store a newly created product.
     * Requires authentication and verification.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);

        $product = $this->service->createProduct(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'message' => 'Product created successfully',
            'data' => new ProductResource($product),
        ], 201);
    }

    /**
     * Update the specified product.
     * Only product owner can update.
     */
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = $this->service->getProductById($id);

        if (! $product) {
            return response()->json([
                'message' => 'Product not found',
            ], 404);
        }

        $this->authorize('update', $product);

        $updated = $this->service->updateProduct($product, $request->validated());

        return response()->json([
            'message' => 'Product updated successfully',
            'data' => new ProductResource($updated),
        ]);
    }

    /**
     * Remove the specified product.
     * Only product owner can delete.
     */
    public function destroy(int $id): JsonResponse
    {
        $product = $this->service->getProductById($id);

        if (! $product) {
            return response()->json([
                'message' => 'Product not found',
            ], 404);
        }

        $this->authorize('delete', $product);

        $this->service->deleteProduct($product);

        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }

    /**
     * Get authenticated user's products.
     */
    public function userProducts(Request $request): JsonResponse
    {
        $products = $this->service->getUserProducts($request->user());

        return response()->json([
            'data' => ProductResource::collection($products),
        ]);
    }
}
