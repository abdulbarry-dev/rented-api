<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Rental;
use App\Models\RentalAvailability;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferTest extends TestCase
{
    use RefreshDatabase;

    protected User $sender;

    protected User $receiver;

    protected Conversation $conversation;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->sender = User::factory()->create();
        $this->receiver = User::factory()->create();

        // Create category and product
        $category = Category::factory()->create();
        $this->product = Product::factory()->create([
            'user_id' => $this->receiver->id,
            'category_id' => $category->id,
            'is_available' => true,
            'price_per_day' => 100.00,
        ]);

        // Create conversation between sender and receiver
        $this->conversation = Conversation::create([
            'product_id' => $this->product->id,
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
        ]);
    }

    /** @test */
    public function user_can_create_rental_offer_in_conversation(): void
    {
        $response = $this->actingAs($this->sender)->postJson("/api/v1/conversations/{$this->conversation->id}/offers", [
            'product_id' => $this->product->id,
            'offer_type' => 'rental',
            'amount' => 150.00,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(10)->format('Y-m-d'),
            'message' => 'Would you accept this rental offer?',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'conversation_id',
                'product',
                'sender',
                'receiver',
                'offer_type',
                'amount',
                'start_date',
                'end_date',
                'message',
                'status',
                'is_pending',
                'is_accepted',
                'is_rejected',
                'is_expired',
                'can_be_responded',
                'expires_at',
                'created_at',
            ],
        ]);

        $this->assertDatabaseHas('offers', [
            'conversation_id' => $this->conversation->id,
            'product_id' => $this->product->id,
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'offer_type' => 'rental',
            'amount' => 150.00,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function user_can_create_purchase_offer_in_conversation(): void
    {
        $response = $this->actingAs($this->sender)->postJson("/api/v1/conversations/{$this->conversation->id}/offers", [
            'product_id' => $this->product->id,
            'offer_type' => 'purchase',
            'amount' => 5000.00,
            'message' => 'Would you sell this item for this price?',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('offers', [
            'conversation_id' => $this->conversation->id,
            'product_id' => $this->product->id,
            'offer_type' => 'purchase',
            'amount' => 5000.00,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function offer_automatically_creates_message_in_conversation(): void
    {
        $this->actingAs($this->sender)->postJson("/api/v1/conversations/{$this->conversation->id}/offers", [
            'product_id' => $this->product->id,
            'offer_type' => 'rental',
            'amount' => 150.00,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(10)->format('Y-m-d'),
            'message' => 'Custom offer message',
        ]);

        $offer = Offer::latest()->first();

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $this->conversation->id,
            'sender_id' => $this->sender->id,
            'offer_id' => $offer->id,
        ]);
    }

    /** @test */
    public function receiver_can_accept_rental_offer(): void
    {
        $offer = Offer::create([
            'conversation_id' => $this->conversation->id,
            'product_id' => $this->product->id,
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'offer_type' => 'rental',
            'amount' => 150.00,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(10),
            'message' => 'Offer message',
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->actingAs($this->receiver)->postJson("/api/v1/conversations/{$this->conversation->id}/offers/{$offer->id}/accept");

        $response->assertStatus(200);

        $this->assertDatabaseHas('offers', [
            'id' => $offer->id,
            'status' => 'accepted',
        ]);

        // Verify rental was created
        $this->assertDatabaseHas('rentals', [
            'product_id' => $this->product->id,
            'renter_id' => $this->sender->id,
            'owner_id' => $this->receiver->id,
            'status' => 'pending',
        ]);

        // Verify dates were blocked in rental availability
        $this->assertDatabaseHas('rental_availabilities', [
            'product_id' => $this->product->id,
            'is_available' => false,
        ]);
    }

    /** @test */
    public function receiver_can_accept_purchase_offer(): void
    {
        $offer = Offer::create([
            'conversation_id' => $this->conversation->id,
            'product_id' => $this->product->id,
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'offer_type' => 'purchase',
            'amount' => 5000.00,
            'message' => 'Purchase offer',
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->actingAs($this->receiver)->postJson("/api/v1/conversations/{$this->conversation->id}/offers/{$offer->id}/accept");

        $response->assertStatus(200);

        $this->assertDatabaseHas('offers', [
            'id' => $offer->id,
            'status' => 'accepted',
        ]);

        // Verify purchase was created
        $this->assertDatabaseHas('purchases', [
            'product_id' => $this->product->id,
            'buyer_id' => $this->sender->id,
            'seller_id' => $this->receiver->id,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function receiver_can_reject_offer(): void
    {
        $offer = Offer::create([
            'conversation_id' => $this->conversation->id,
            'product_id' => $this->product->id,
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'offer_type' => 'rental',
            'amount' => 150.00,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(10),
            'message' => 'Offer message',
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->actingAs($this->receiver)->postJson("/api/v1/conversations/{$this->conversation->id}/offers/{$offer->id}/reject");

        $response->assertStatus(200);

        $this->assertDatabaseHas('offers', [
            'id' => $offer->id,
            'status' => 'rejected',
        ]);
    }

    /** @test */
    public function sender_cannot_accept_their_own_offer(): void
    {
        $offer = Offer::create([
            'conversation_id' => $this->conversation->id,
            'product_id' => $this->product->id,
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'offer_type' => 'rental',
            'amount' => 150.00,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(10),
            'message' => 'Offer message',
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->actingAs($this->sender)->postJson("/api/v1/conversations/{$this->conversation->id}/offers/{$offer->id}/accept");

        $response->assertStatus(403);
    }

    /** @test */
    public function sender_cannot_reject_their_own_offer(): void
    {
        $offer = Offer::create([
            'conversation_id' => $this->conversation->id,
            'product_id' => $this->product->id,
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'offer_type' => 'rental',
            'amount' => 150.00,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(10),
            'message' => 'Offer message',
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->actingAs($this->sender)->postJson("/api/v1/conversations/{$this->conversation->id}/offers/{$offer->id}/reject");

        $response->assertStatus(403);
    }

    /** @test */
    public function user_cannot_accept_expired_offer(): void
    {
        $offer = Offer::create([
            'conversation_id' => $this->conversation->id,
            'product_id' => $this->product->id,
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'offer_type' => 'rental',
            'amount' => 150.00,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(10),
            'message' => 'Offer message',
            'status' => 'pending',
            'expires_at' => now()->subDays(1), // Already expired
        ]);

        $response = $this->actingAs($this->receiver)->postJson("/api/v1/conversations/{$this->conversation->id}/offers/{$offer->id}/accept");

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'This offer has expired or cannot be responded to.',
        ]);
    }

    /** @test */
    public function user_cannot_accept_already_accepted_offer(): void
    {
        $offer = Offer::create([
            'conversation_id' => $this->conversation->id,
            'product_id' => $this->product->id,
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'offer_type' => 'rental',
            'amount' => 150.00,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(10),
            'message' => 'Offer message',
            'status' => 'accepted', // Already accepted
            'expires_at' => now()->addDays(7),
            'responded_at' => now(),
        ]);

        $response = $this->actingAs($this->receiver)->postJson("/api/v1/conversations/{$this->conversation->id}/offers/{$offer->id}/accept");

        $response->assertStatus(422);
    }

    /** @test */
    public function user_can_list_all_offers_in_conversation(): void
    {
        // Create multiple offers
        Offer::factory()->count(3)->create([
            'conversation_id' => $this->conversation->id,
            'product_id' => $this->product->id,
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
        ]);

        $response = $this->actingAs($this->sender)->getJson("/api/v1/conversations/{$this->conversation->id}/offers");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'conversation_id',
                    'product',
                    'sender',
                    'receiver',
                    'offer_type',
                    'amount',
                    'status',
                ],
            ],
            'meta' => ['current_page', 'total', 'per_page'],
        ]);
    }

    /** @test */
    public function user_can_view_single_offer(): void
    {
        $offer = Offer::create([
            'conversation_id' => $this->conversation->id,
            'product_id' => $this->product->id,
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
            'offer_type' => 'rental',
            'amount' => 150.00,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(10),
            'message' => 'Offer message',
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->actingAs($this->sender)->getJson("/api/v1/conversations/{$this->conversation->id}/offers/{$offer->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'conversation_id',
                'product',
                'sender',
                'receiver',
                'offer_type',
                'amount',
                'start_date',
                'end_date',
                'message',
                'status',
            ],
        ]);
    }

    /** @test */
    public function non_participant_cannot_view_offers_in_conversation(): void
    {
        $outsider = User::factory()->create();

        $response = $this->actingAs($outsider)->getJson("/api/v1/conversations/{$this->conversation->id}/offers");

        $response->assertStatus(403);
    }

    /** @test */
    public function rental_offer_requires_start_and_end_dates(): void
    {
        $response = $this->actingAs($this->sender)->postJson("/api/v1/conversations/{$this->conversation->id}/offers", [
            'product_id' => $this->product->id,
            'offer_type' => 'rental',
            'amount' => 150.00,
            // Missing start_date and end_date
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['start_date', 'end_date']);
    }

    /** @test */
    public function purchase_offer_does_not_require_dates(): void
    {
        $response = $this->actingAs($this->sender)->postJson("/api/v1/conversations/{$this->conversation->id}/offers", [
            'product_id' => $this->product->id,
            'offer_type' => 'purchase',
            'amount' => 5000.00,
            // No dates required for purchase
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function cannot_create_offer_for_unavailable_product(): void
    {
        $this->product->update(['is_available' => false]);

        $response = $this->actingAs($this->sender)->postJson("/api/v1/conversations/{$this->conversation->id}/offers", [
            'product_id' => $this->product->id,
            'offer_type' => 'rental',
            'amount' => 150.00,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(10)->format('Y-m-d'),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['product_id']);
    }

    /** @test */
    public function cannot_create_rental_offer_for_blocked_dates(): void
    {
        $startDate = now()->addDays(5);
        $endDate = now()->addDays(10);

        // Block one of the dates
        RentalAvailability::create([
            'product_id' => $this->product->id,
            'date' => $startDate->copy()->addDays(2),
            'is_available' => false,
            'reason' => 'maintenance',
        ]);

        $response = $this->actingAs($this->sender)->postJson("/api/v1/conversations/{$this->conversation->id}/offers", [
            'product_id' => $this->product->id,
            'offer_type' => 'rental',
            'amount' => 150.00,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function offer_amount_must_be_positive(): void
    {
        $response = $this->actingAs($this->sender)->postJson("/api/v1/conversations/{$this->conversation->id}/offers", [
            'product_id' => $this->product->id,
            'offer_type' => 'rental',
            'amount' => -50.00, // Negative amount
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(10)->format('Y-m-d'),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount']);
    }

    /** @test */
    public function offer_expires_after_7_days_by_default(): void
    {
        $this->actingAs($this->sender)->postJson("/api/v1/conversations/{$this->conversation->id}/offers", [
            'product_id' => $this->product->id,
            'offer_type' => 'rental',
            'amount' => 150.00,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(10)->format('Y-m-d'),
        ]);

        $offer = Offer::latest()->first();

        $this->assertNotNull($offer->expires_at);
        $this->assertTrue($offer->expires_at->diffInDays(now()) === 7);
    }
}
