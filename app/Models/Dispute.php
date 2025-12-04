<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dispute extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'rental_id',
        'purchase_id',
        'reported_by',
        'reported_against',
        'dispute_type',
        'status',
        'description',
        'evidence',
        'resolution',
        'resolved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'evidence' => 'array',
        'resolved_at' => 'datetime',
    ];

    /**
     * Get the rental.
     */
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    /**
     * Get the purchase.
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Get the user who reported the dispute.
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    /**
     * Get the user who the dispute is against.
     */
    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_against');
    }
}
