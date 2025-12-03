<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class CategoryRepository
{
    /**
     * Get all active categories.
     */
    public function getAll(): Collection
    {
        return Cache::remember('categories.all', 3600, function () {
            return Category::where('is_active', true)
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Find category by ID.
     */
    public function findById(int $id): ?Category
    {
        return Cache::remember("categories.{$id}", 3600, function () use ($id) {
            return Category::find($id);
        });
    }

    /**
     * Find category by slug.
     */
    public function findBySlug(string $slug): ?Category
    {
        return Category::where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Create a new category.
     */
    public function create(array $data): Category
    {
        return Category::create($data);
    }

    /**
     * Update an existing category.
     */
    public function update(Category $category, array $data): bool
    {
        return $category->update($data);
    }

    /**
     * Delete a category.
     */
    public function delete(Category $category): bool
    {
        return $category->delete();
    }
}
