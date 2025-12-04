<?php

namespace App\Services;

use App\Repositories\CategoryRepository;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    public function __construct(
        private CategoryRepository $repository
    ) {}

    /**
     * Get all active categories.
     */
    public function getAllCategories(): Collection
    {
        return $this->repository->getAll();
    }

    /**
     * Get category by ID.
     */
    public function getCategoryById(int $id): ?object
    {
        return $this->repository->findById($id);
    }

    /**
     * Get category by slug.
     */
    public function getCategoryBySlug(string $slug): ?object
    {
        return $this->repository->findBySlug($slug);
    }
}
