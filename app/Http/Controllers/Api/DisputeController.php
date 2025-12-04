<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDisputeRequest;
use App\Http\Resources\DisputeResource;
use App\Services\DisputeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DisputeController extends Controller
{
    public function __construct(
        private DisputeService $service
    ) {}

    /**
     * Get user's disputes.
     */
    public function index(): AnonymousResourceCollection
    {
        $disputes = $this->service->getUserDisputes(auth()->user());

        return DisputeResource::collection($disputes);
    }

    /**
     * Get a specific dispute.
     */
    public function show(int $id): DisputeResource|JsonResponse
    {
        $dispute = $this->service->getDisputeById($id);

        if (! $dispute) {
            return response()->json([
                'message' => 'Dispute not found.',
            ], 404);
        }

        if (! $this->service->isUserInvolved($dispute, auth()->user())) {
            return response()->json([
                'message' => 'You do not have access to this dispute.',
            ], 403);
        }

        return new DisputeResource($dispute);
    }

    /**
     * Create a new dispute.
     */
    public function store(StoreDisputeRequest $request): DisputeResource
    {
        $dispute = $this->service->createDispute(auth()->user(), $request->validated());

        return new DisputeResource($dispute);
    }

    /**
     * Update dispute status.
     */
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:investigating,resolved,closed',
        ]);

        $dispute = $this->service->getDisputeById($id);

        if (! $dispute) {
            return response()->json([
                'message' => 'Dispute not found.',
            ], 404);
        }

        $this->service->updateDisputeStatus($dispute, $request->status);

        return response()->json([
            'message' => 'Dispute status updated.',
        ]);
    }

    /**
     * Resolve a dispute.
     */
    public function resolve(int $id, Request $request): JsonResponse
    {
        $request->validate([
            'resolution' => 'required|string|max:2000',
        ]);

        $dispute = $this->service->getDisputeById($id);

        if (! $dispute) {
            return response()->json([
                'message' => 'Dispute not found.',
            ], 404);
        }

        $this->service->resolveDispute($dispute, $request->resolution);

        return response()->json([
            'message' => 'Dispute resolved successfully.',
        ]);
    }
}
