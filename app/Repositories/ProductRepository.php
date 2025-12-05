<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ProductRepository
{
    /**
     * Get all products with pagination.
     * Eager loads category and user relationships for efficiency.
     */
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        $page = request()->get('page', 1);
        $cacheKey = "products.paginated.page.{$page}.per_page.{$perPage}";

        return Cache::remember($cacheKey, 600, function () use ($perPage) {
            return Product::with(['category', 'user'])
                ->where('is_available', true)
                ->where('verification_status', 'approved')
                ->latest()
                ->paginate($perPage);
        });
    }

    /**
     * Get all products without pagination.
     */
    public function getAll(): Collection
    {
        return Cache::remember('products.all', 600, function () {
            return Product::with(['category', 'user'])
                ->where('is_available', true)
                ->where('verification_status', 'approved')
                ->latest()
                ->get();
        });
    }

    /**
     * Find product by ID.
     * Eager loads category and user relationships.
     */
    public function findById(int $id): ?Product
    {
        return Cache::remember("products.{$id}", 600, function () use ($id) {
            return Product::with(['category', 'user'])
                ->find($id);
        });
    }

    /**
     * Create a new product.
     */
    public function create(array $data): Product
    {
        return Product::create($data);
    }

    /**
     * Update an existing product.
     */
    public function update(Product $product, array $data): bool
    {
        return $product->update($data);
    }

    /**
     * Delete a product.
     */
    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    /**
     * Get products by user ID.
     */
    public function getByUserId(int $userId): Collection
    {
        return Product::with('category')
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    /**
     * Get products by category ID.
     */
    public function getByCategoryId(int $categoryId): Collection
    {
        return Product::with('category')
            ->where('category_id', $categoryId)
            ->where('is_available', true)
            ->latest()
            ->get();
    }
}
