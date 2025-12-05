<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOfferRequest;
use App\Http\Resources\OfferResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Rental;
use App\Models\RentalAvailability;
use Carbon\CarbonPeriod;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('viewAny', Offer::class);

        if ($conversation->user_one_id !== auth()->id() && $conversation->user_two_id !== auth()->id()) {
            return response()->json(['message' => 'You are not a participant in this conversation.'], 403);
        }

        $offers = $conversation->offers()->with(['sender', 'receiver', 'product'])->latest()->paginate($request->input('per_page', 15));

        return response()->json([
            'message' => 'Offers retrieved successfully',
            'data' => OfferResource::collection($offers->items()),
            'meta' => [
                'current_page' => $offers->currentPage(),
                'from' => $offers->firstItem(),
                'last_page' => $offers->lastPage(),
                'per_page' => $offers->perPage(),
                'to' => $offers->lastItem(),
                'total' => $offers->total(),
            ],
            'links' => [
                'first' => $offers->url(1),
                'last' => $offers->url($offers->lastPage()),
                'prev' => $offers->previousPageUrl(),
                'next' => $offers->nextPageUrl(),
            ],
        ]);
    }

    public function store(StoreOfferRequest $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('create', Offer::class);

        $user = auth()->user();
        $product = Product::findOrFail($request->product_id);

        $receiverId = $conversation->user_one_id === $user->id ? $conversation->user_two_id : $conversation->user_one_id;

        $offer = Offer::create([
            'conversation_id' => $conversation->id,
            'product_id' => $product->id,
            'sender_id' => $user->id,
            'receiver_id' => $receiverId,
            'offer_type' => $request->offer_type,
            'amount' => $request->amount,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'message' => $request->message,
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'content' => $request->message ?? 'I\'ve sent you an offer for '.$product->title,
            'offer_id' => $offer->id,
        ]);

        $conversation->update(['last_message_at' => now()]);
        $offer->load(['sender', 'receiver', 'product']);

        return response()->json(['message' => 'Offer created successfully', 'data' => new OfferResource($offer)], 201);
    }

    public function show(Conversation $conversation, Offer $offer): JsonResponse
    {
        $this->authorize('view', $offer);

        if ($offer->conversation_id !== $conversation->id) {
            return response()->json(['message' => 'Offer not found in this conversation.'], 404);
        }

        $offer->load(['sender', 'receiver', 'product']);

        return response()->json(['message' => 'Offer retrieved successfully', 'data' => new OfferResource($offer)]);
    }

    public function accept(Conversation $conversation, Offer $offer): JsonResponse
    {
        $this->authorize('accept', $offer);

        if ($offer->conversation_id !== $conversation->id) {
            return response()->json(['message' => 'Offer not found in this conversation.'], 404);
        }

        if (! $offer->canBeResponded()) {
            return response()->json(['message' => 'This offer cannot be accepted.'], 422);
        }

        if ($offer->offer_type === 'rental') {
            $blockedDates = RentalAvailability::where('product_id', $offer->product_id)
                ->whereBetween('blocked_date', [$offer->start_date, $offer->end_date])
                ->exists();

            if ($blockedDates) {
                return response()->json(['message' => 'Product is no longer available for the selected dates.'], 422);
            }
        }

        $offer->update(['status' => 'accepted', 'responded_at' => now()]);

        if ($offer->offer_type === 'rental') {
            $rental = Rental::create([
                'product_id' => $offer->product_id,
                'user_id' => $offer->sender_id,
                'start_date' => $offer->start_date,
                'end_date' => $offer->end_date,
                'total_price' => $offer->amount,
                'status' => 'pending',
                'delivery_required' => false,
                'notes' => 'Created from accepted offer #'.$offer->id,
            ]);

            $dates = CarbonPeriod::create($offer->start_date, $offer->end_date);
            foreach ($dates as $date) {
                RentalAvailability::create([
                    'product_id' => $offer->product_id,
                    'blocked_date' => $date,
                    'block_type' => 'booked',
                    'rental_id' => $rental->id,
                    'notes' => 'Blocked by offer #'.$offer->id,
                ]);
            }
        } else {
            Purchase::create([
                'product_id' => $offer->product_id,
                'user_id' => $offer->sender_id,
                'purchase_price' => $offer->amount,
                'status' => 'pending',
                'delivery_required' => false,
                'notes' => 'Created from accepted offer #'.$offer->id,
            ]);
        }

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => auth()->id(),
            'content' => 'Offer accepted!',
        ]);

        $conversation->update(['last_message_at' => now()]);
        $offer->load(['sender', 'receiver', 'product']);

        return response()->json(['message' => 'Offer accepted successfully', 'data' => new OfferResource($offer)]);
    }

    public function reject(Conversation $conversation, Offer $offer): JsonResponse
    {
        $this->authorize('reject', $offer);

        if ($offer->conversation_id !== $conversation->id) {
            return response()->json(['message' => 'Offer not found in this conversation.'], 404);
        }

        if (! $offer->canBeResponded()) {
            return response()->json(['message' => 'This offer cannot be rejected.'], 422);
        }

        $offer->update(['status' => 'rejected', 'responded_at' => now()]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => auth()->id(),
            'content' => 'Offer declined.',
        ]);

        $conversation->update(['last_message_at' => now()]);
        $offer->load(['sender', 'receiver', 'product']);

        return response()->json(['message' => 'Offer rejected successfully', 'data' => new OfferResource($offer)]);
    }
}
