<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use App\Repositories\ProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    public function __construct(
        private ProductRepository $repository
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
            $data['thumbnail'] = $this->uploadFile($data['thumbnail'], 'products/thumbnails');
        }

        // Handle multiple images upload
        if (isset($data['images']) && is_array($data['images'])) {
            $imagePaths = [];
            foreach ($data['images'] as $image) {
                $imagePaths[] = $this->uploadFile($image, 'products/images');
            }
            $data['images'] = $imagePaths;
        }

        // Add user_id
        $data['user_id'] = $user->id;

        // Set defaults
        $data['is_available'] = $data['is_available'] ?? true;
        $data['is_for_sale'] = $data['is_for_sale'] ?? false;

        return $this->repository->create($data);
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
                Storage::disk('public')->delete($product->thumbnail);
            }
            $data['thumbnail'] = $this->uploadFile($data['thumbnail'], 'products/thumbnails');
        }

        // Handle multiple images upload
        if (isset($data['images']) && is_array($data['images'])) {
            // Delete old images
            if ($product->images && is_array($product->images)) {
                foreach ($product->images as $oldImage) {
                    Storage::disk('public')->delete($oldImage);
                }
            }

            $imagePaths = [];
            foreach ($data['images'] as $image) {
                $imagePaths[] = $this->uploadFile($image, 'products/images');
            }
            $data['images'] = $imagePaths;
        }

        $this->repository->update($product, $data);

        return $product->fresh();
    }

    /**
     * Delete a product.
     */
    public function deleteProduct(Product $product): bool
    {
        // Delete associated files
        if ($product->thumbnail) {
            Storage::disk('public')->delete($product->thumbnail);
        }

        if ($product->images && is_array($product->images)) {
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        return $this->repository->delete($product);
    }

    /**
     * Upload file to storage.
     */
    private function uploadFile(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'public');
    }
}
