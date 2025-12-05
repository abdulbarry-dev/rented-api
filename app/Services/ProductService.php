<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use App\Repositories\ProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ProductService
{
    public function __construct(
        private ProductRepository $repository,
        private ImageUploadService $imageUploadService
    ) {}

    /**
     * Get all products with pagination.
     */
    public function getAllProducts(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getAllPaginated($perPage);
    }

    /**
     * Get product by ID.
     */
    public function getProductById(int $id): ?Product
    {
        return $this->repository->findById($id);
    }

    /**
     * Get products by category.
     */
    public function getProductsByCategory(int $categoryId): Collection
    {
        return $this->repository->getByCategoryId($categoryId);
    }

    /**
     * Get user's products.
     */
    public function getUserProducts(User $user): Collection
    {
        return $this->repository->getByUserId($user->id);
    }

    /**
     * Create a new product.
     */
    public function createProduct(User $user, array $data): Product
    {
        // Handle thumbnail upload
        if (isset($data['thumbnail'])) {
            $data['thumbnail'] = $this->imageUploadService->uploadProductThumbnail($data['thumbnail']);
        }

        // Handle multiple images upload
        if (isset($data['images']) && is_array($data['images'])) {
            $data['images'] = $this->imageUploadService->uploadProductImages($data['images']);
        }

        // Add user_id
        $data['user_id'] = $user->id;

        // Set defaults
        $data['is_available'] = $data['is_available'] ?? true;
        $data['is_for_sale'] = $data['is_for_sale'] ?? false;

        $product = $this->repository->create($data);

        // Clear product caches
        $this->clearProductCaches();

        return $product;
    }

    /**
     * Update a product.
     */
    public function updateProduct(Product $product, array $data): Product
    {
        // Handle thumbnail upload
        if (isset($data['thumbnail'])) {
            // Delete old thumbnail
            if ($product->thumbnail) {
                $this->imageUploadService->delete($product->thumbnail);
            }
            $data['thumbnail'] = $this->imageUploadService->uploadProductThumbnail($data['thumbnail']);
        }

        // Handle multiple images upload
        if (isset($data['images']) && is_array($data['images'])) {
            // Delete old images
            if ($product->images && is_array($product->images)) {
                $this->imageUploadService->delete($product->images);
            }

            $data['images'] = $this->imageUploadService->uploadProductImages($data['images']);
        }

        $this->repository->update($product, $data);

        // Clear product caches
        $this->clearProductCaches($product->id);

        return $product->fresh();
    }

    /**
     * Delete a product.
     */
    public function deleteProduct(Product $product): bool
    {
        $productId = $product->id;

        // Delete associated files
        if ($product->thumbnail) {
            $this->imageUploadService->delete($product->thumbnail);
        }

        if ($product->images && is_array($product->images)) {
            $this->imageUploadService->delete($product->images);
        }

        $result = $this->repository->delete($product);

        // Clear product caches
        $this->clearProductCaches($productId);

        return $result;
    }

    /**
     * Clear product-related caches.
     */
    private function clearProductCaches(?int $productId = null): void
    {
        // Clear all products cache
        Cache::forget('products.all');

        // Clear paginated caches (clear first 10 pages)
        for ($page = 1; $page <= 10; $page++) {
            Cache::forget("products.paginated.page.{$page}.per_page.15");
        }

        // Clear specific product cache if ID provided
        if ($productId) {
            Cache::forget("products.{$productId}");
        }
    }
}
