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
     * But allows product owners to see their own products even if not approved.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        // Try to get approved product first
        $product = $this->service->getProductById($id);

        // If not found, try to authenticate user from token (even on public route)
        // This allows owners to see their products even if not approved or unavailable
        if (! $product && $request->bearerToken()) {
            try {
                // Manually authenticate using Sanctum guard from the request
                $user = auth('sanctum')->setRequest($request)->user();

                if ($user) {
                    $product = Product::with(['category', 'user'])
                        ->where('id', $id)
                        ->where('user_id', $user->id)
                        ->first();
                }
            } catch (\Exception $e) {
                // Token invalid or expired, continue as guest
            }
        }

        if (! $product) {
            return response()->json([
                'message' => 'Product not found',
            ], 404);
        }

        // Load relationships for public viewing
        $product->load(['category', 'user']);

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
     * Allows owners to update their products even if not approved or unavailable.
     */
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        // Try to get approved product first
        $product = $this->service->getProductById($id);

        // If not found, check if it belongs to the authenticated user
        // This allows owners to update their products even if not approved or unavailable
        if (! $product) {
            $product = Product::with(['category', 'user'])
                ->where('id', $id)
                ->where('user_id', $request->user()->id)
                ->first();
        }

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
     * Allows owners to delete their products even if not approved or unavailable.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        // Try to get approved product first
        $product = $this->service->getProductById($id);

        // If not found, check if it belongs to the authenticated user
        // This allows owners to delete their products even if not approved or unavailable
        if (! $product) {
            $product = Product::with(['category', 'user'])
                ->where('id', $id)
                ->where('user_id', $request->user()->id)
                ->first();
        }

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
