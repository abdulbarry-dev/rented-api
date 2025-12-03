<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
    public function __construct(
        private ProductRepository $repository
    ) {
    }

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
    public function getProductById(int $id): ?object
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
}
