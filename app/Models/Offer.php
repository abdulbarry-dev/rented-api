<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Offer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'conversation_id',
        'product_id',
        'sender_id',
        'receiver_id',
        'offer_type',
        'amount',
        'start_date',
        'end_date',
        'message',
        'status',
        'expires_at',
        'responded_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'expires_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    /**
     * Get the conversation that owns the offer.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the product that the offer is for.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who sent the offer.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the user who received the offer.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Get the message associated with the offer.
     */
    public function messageRecord(): HasOne
    {
        return $this->hasOne(Message::class);
    }

    /**
     * Scope a query to only include pending offers.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include accepted offers.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope a query to only include rejected offers.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope a query to only include expired offers.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * Scope a query to filter by offer type.
     */
    public function scopeOfferType($query, string $type)
    {
        return $query->where('offer_type', $type);
    }

    /**
     * Check if the offer is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the offer is accepted.
     */
    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    /**
     * Check if the offer is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if the offer is expired.
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired' || ($this->expires_at && $this->expires_at->isPast());
    }

    /**
     * Check if the offer can be responded to.
     */
    public function canBeResponded(): bool
    {
        return $this->isPending() && ! $this->isExpired();
    }
}
