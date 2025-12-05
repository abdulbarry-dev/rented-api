<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'price_per_day',
        'price_per_week',
        'price_per_month',
        'is_for_sale',
        'sale_price',
        'is_available',
        'verification_status',
        'rejection_reason',
        'verified_at',
        'thumbnail',
        'images',
        'location_address',
        'location_city',
        'location_state',
        'location_country',
        'location_zip',
        'location_latitude',
        'location_longitude',
        'delivery_available',
        'delivery_fee',
        'delivery_radius_km',
        'pickup_available',
        'product_condition',
        'security_deposit',
        'min_rental_days',
        'max_rental_days',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price_per_day' => 'decimal:2',
        'price_per_week' => 'decimal:2',
        'price_per_month' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'is_for_sale' => 'boolean',
        'is_available' => 'boolean',
        'delivery_available' => 'boolean',
        'pickup_available' => 'boolean',
        'images' => 'array',
        'location_latitude' => 'decimal:8',
        'location_longitude' => 'decimal:8',
        'min_rental_days' => 'integer',
        'max_rental_days' => 'integer',
        'delivery_radius_km' => 'integer',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the user that owns the product.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category of the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all rentals for the product.
     */
    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    /**
     * Get all purchases for the product.
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Get all reviews for the product.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get all favourites for the product.
     */
    public function favourites(): HasMany
    {
        return $this->hasMany(Favourite::class);
    }

    /**
     * Get the full URL for the thumbnail.
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (! $this->thumbnail) {
            return null;
        }

        // If it's already a full URL (http/https), return as is
        if (str_starts_with($this->thumbnail, 'http://') || str_starts_with($this->thumbnail, 'https://')) {
            return $this->thumbnail;
        }

        return asset('storage/'.$this->thumbnail);
    }

    /**
     * Get full URLs for all product images.
     */
    public function getImageUrlsAttribute(): array
    {
        if (! $this->images || ! is_array($this->images)) {
            return [];
        }

        return array_map(function ($imagePath) {
            // If it's already a full URL (http/https), return as is
            if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://')) {
                return $imagePath;
            }

            return asset('storage/'.$imagePath);
        }, $this->images);
    }
}
