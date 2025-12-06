<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository
{
    /**
     * Get all products with pagination.
     * Eager loads category and user relationships for efficiency.
     */
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Product::with(['category', 'user'])
            ->where('is_available', true)
            ->where('verification_status', 'approved')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get all products without pagination.
     */
    public function getAll(): Collection
    {
        return Product::with(['category', 'user'])
            ->where('is_available', true)
            ->where('verification_status', 'approved')
            ->latest()
            ->get();
    }

    /**
     * Find product by ID.
     * Eager loads category and user relationships.
     * Only returns approved and available products for public access.
     */
    public function findById(int $id): ?Product
    {
        return Product::with(['category', 'user'])
            ->where('id', $id)
            ->where('is_available', true)
            ->where('verification_status', 'approved')
            ->first();
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
     * Only returns approved and available products.
     */
    public function getByCategoryId(int $categoryId): Collection
    {
        return Product::with('category')
            ->where('category_id', $categoryId)
            ->where('is_available', true)
            ->where('verification_status', 'approved')
            ->latest()
            ->get();
    }
}
