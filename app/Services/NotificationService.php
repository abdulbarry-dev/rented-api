<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Get user's notifications with pagination.
     */
    public function getUserNotifications(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get unread notifications.
     */
    public function getUnreadNotifications(User $user): Collection
    {
        return Notification::where('user_id', $user->id)
            ->unread()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get unread count.
     */
    public function getUnreadCount(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->unread()
            ->count();
    }

    /**
     * Create a notification and send FCM push notification.
     */
    public function create(User $user, string $type, string $title, string $message, ?array $data = null): Notification
    {
        // Create database notification
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);

        // Send FCM push notification
        try {
            $fcmService = app(FcmNotificationService::class);
            $fcmService->sendToUser($user, $title, $message, $data ?? [], $type);
        } catch (\Exception $e) {
            // Log error but don't fail the notification creation
            Log::warning('Failed to send FCM notification', [
                'user_id' => $user->id,
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $notification;
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Notification $notification): Notification
    {
        $notification->markAsRead();

        return $notification->fresh();
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * Delete notification.
     */
    public function delete(Notification $notification): bool
    {
        return $notification->delete();
    }

    /**
     * Delete all read notifications.
     */
    public function deleteAllRead(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->read()
            ->delete();
    }

    // ========== Notification Triggers ==========

    /**
     * Notify when product is approved.
     */
    public function notifyProductApproved(User $user, int $productId, string $productTitle): Notification
    {
        return $this->create(
            $user,
            'product_approved',
            'Product Approved',
            "Your product \"{$productTitle}\" has been approved and is now live!",
            ['product_id' => $productId]
        );
    }

    /**
     * Notify when product is rejected.
     */
    public function notifyProductRejected(User $user, int $productId, string $productTitle, ?string $reason = null): Notification
    {
        $message = "Your product \"{$productTitle}\" was rejected.";
        if ($reason) {
            $message .= " Reason: {$reason}";
        }

        return $this->create(
            $user,
            'product_rejected',
            'Product Rejected',
            $message,
            ['product_id' => $productId, 'reason' => $reason]
        );
    }

    /**
     * Notify when rental request is received.
     */
    public function notifyRentalRequested(User $owner, int $rentalId, int $productId, string $productTitle, string $renterName): Notification
    {
        return $this->create(
            $owner,
            'rental_requested',
            'New Rental Request',
            "{$renterName} wants to rent your \"{$productTitle}\"",
            ['rental_id' => $rentalId, 'product_id' => $productId]
        );
    }

    /**
     * Notify when rental is confirmed.
     */
    public function notifyRentalConfirmed(User $renter, int $rentalId, int $productId, string $productTitle): Notification
    {
        return $this->create(
            $renter,
            'rental_confirmed',
            'Rental Confirmed',
            "Your rental of \"{$productTitle}\" has been confirmed!",
            ['rental_id' => $rentalId, 'product_id' => $productId]
        );
    }

    /**
     * Notify when rental is completed.
     */
    public function notifyRentalCompleted(User $user, int $rentalId, int $productId, string $productTitle): Notification
    {
        return $this->create(
            $user,
            'rental_completed',
            'Rental Completed',
            "Your rental of \"{$productTitle}\" is now complete. Please leave a review!",
            ['rental_id' => $rentalId, 'product_id' => $productId]
        );
    }

    /**
     * Notify when purchase order is placed.
     */
    public function notifyPurchaseOrdered(User $seller, int $purchaseId, int $productId, string $productTitle, string $buyerName): Notification
    {
        return $this->create(
            $seller,
            'purchase_ordered',
            'New Purchase Order',
            "{$buyerName} ordered your \"{$productTitle}\"",
            ['purchase_id' => $purchaseId, 'product_id' => $productId]
        );
    }

    /**
     * Notify when purchase is completed.
     */
    public function notifyPurchaseCompleted(User $buyer, int $purchaseId, int $productId, string $productTitle): Notification
    {
        return $this->create(
            $buyer,
            'purchase_completed',
            'Purchase Completed',
            "Your purchase of \"{$productTitle}\" is complete!",
            ['purchase_id' => $purchaseId, 'product_id' => $productId]
        );
    }

    /**
     * Notify when new message is received.
     */
    public function notifyNewMessage(User $receiver, int $conversationId, int $productId, string $senderName): Notification
    {
        return $this->create(
            $receiver,
            'new_message',
            'New Message',
            "{$senderName} sent you a message",
            ['conversation_id' => $conversationId, 'product_id' => $productId]
        );
    }

    /**
     * Notify when offer is received.
     */
    public function notifyOfferReceived(User $receiver, int $offerId, int $productId, string $productTitle, float $amount): Notification
    {
        return $this->create(
            $receiver,
            'offer_received',
            'New Offer',
            "You received an offer of \${$amount} for \"{$productTitle}\"",
            ['offer_id' => $offerId, 'product_id' => $productId, 'amount' => $amount]
        );
    }

    /**
     * Notify when offer is accepted.
     */
    public function notifyOfferAccepted(User $sender, int $offerId, int $productId, string $productTitle): Notification
    {
        return $this->create(
            $sender,
            'offer_accepted',
            'Offer Accepted',
            "Your offer for \"{$productTitle}\" was accepted!",
            ['offer_id' => $offerId, 'product_id' => $productId]
        );
    }

    /**
     * Notify when offer is rejected.
     */
    public function notifyOfferRejected(User $sender, int $offerId, int $productId, string $productTitle): Notification
    {
        return $this->create(
            $sender,
            'offer_rejected',
            'Offer Rejected',
            "Your offer for \"{$productTitle}\" was rejected",
            ['offer_id' => $offerId, 'product_id' => $productId]
        );
    }

    /**
     * Notify when review is received.
     */
    public function notifyReviewReceived(User $productOwner, int $reviewId, int $productId, string $productTitle, int $rating): Notification
    {
        return $this->create(
            $productOwner,
            'review_received',
            'New Review',
            "You received a {$rating}-star review for \"{$productTitle}\"",
            ['review_id' => $reviewId, 'product_id' => $productId, 'rating' => $rating]
        );
    }

    /**
     * Notify when dispute is opened.
     */
    public function notifyDisputeOpened(User $respondent, int $disputeId, string $disputeType): Notification
    {
        return $this->create(
            $respondent,
            'dispute_opened',
            'Dispute Opened',
            "A dispute has been opened regarding your {$disputeType}",
            ['dispute_id' => $disputeId]
        );
    }

    /**
     * Notify when dispute is resolved.
     */
    public function notifyDisputeResolved(User $user, int $disputeId, string $resolution): Notification
    {
        return $this->create(
            $user,
            'dispute_resolved',
            'Dispute Resolved',
            "Your dispute has been resolved: {$resolution}",
            ['dispute_id' => $disputeId]
        );
    }
}
