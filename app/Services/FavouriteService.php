<?php

namespace App\Services;

use App\Models\Favourite;
use App\Models\User;
use App\Repositories\FavouriteRepository;
use Illuminate\Database\Eloquent\Collection;

class FavouriteService
{
    public function __construct(
        private FavouriteRepository $repository
    ) {}

    /**
     * Get all favourites by the user.
     */
    public function getUserFavourites(User $user): Collection
    {
        return $this->repository->getByUserId($user->id);
    }

    /**
     * Toggle favourite status for a product.
     */
    public function toggleFavourite(User $user, int $productId): array
    {
        $favourite = $this->repository->findByUserAndProduct($user->id, $productId);

        if ($favourite) {
            $this->repository->delete($favourite);

            return [
                'favourited' => false,
                'message' => 'Product removed from favourites.',
            ];
        }

        $this->repository->create([
            'user_id' => $user->id,
            'product_id' => $productId,
        ]);

        return [
            'favourited' => true,
            'message' => 'Product added to favourites.',
        ];
    }

    /**
     * Check if user has favourited a product.
     */
    public function hasFavourited(User $user, int $productId): bool
    {
        return $this->repository->hasFavourited($user->id, $productId);
    }

    /**
     * Remove a product from favourites.
     */
    public function removeFavourite(User $user, int $productId): bool
    {
        $favourite = $this->repository->findByUserAndProduct($user->id, $productId);

        if (! $favourite) {
            throw new \Exception('Product is not in your favourites.');
        }

        return $this->repository->delete($favourite);
    }
}
