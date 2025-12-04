<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserVerification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'verification_images',
        'status',
        'admin_notes',
        'submitted_at',
        'reviewed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'verification_images' => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the user that owns the verification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get ID front image path.
     */
    public function getIdFrontPathAttribute(): ?string
    {
        return $this->verification_images['id_front'] ?? null;
    }

    /**
     * Get ID back image path.
     */
    public function getIdBackPathAttribute(): ?string
    {
        return $this->verification_images['id_back'] ?? null;
    }

    /**
     * Get selfie image path.
     */
    public function getSelfiePathAttribute(): ?string
    {
        return $this->verification_images['selfie'] ?? null;
    }

    /**
     * Check if ID front image exists.
     */
    public function hasIdFront(): bool
    {
        return ! empty($this->id_front_path);
    }

    /**
     * Check if ID back image exists.
     */
    public function hasIdBack(): bool
    {
        return ! empty($this->id_back_path);
    }

    /**
     * Check if selfie image exists.
     */
    public function hasSelfie(): bool
    {
        return ! empty($this->selfie_path);
    }

    /**
     * Check if verification is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if verification is verified.
     */
    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    /**
     * Check if verification is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
