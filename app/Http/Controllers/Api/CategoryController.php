<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(
        private CategoryService $service
    ) {}

    /**
     * Display a listing of categories.
     * Public endpoint - no authentication required.
     */
    public function index(): JsonResponse
    {
        $categories = $this->service->getAllCategories();

        return response()->json([
            'data' => CategoryResource::collection($categories),
        ]);
    }

    /**
     * Display the specified category.
     * Public endpoint - no authentication required.
     */
    public function show(int $id): JsonResponse
    {
        $category = $this->service->getCategoryById($id);

        if (! $category) {
            return response()->json([
                'message' => 'Category not found',
            ], 404);
        }

        return response()->json([
            'data' => new CategoryResource($category),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * Admin functionality - not part of current phase.
     */
    public function store()
    {
        // Admin functionality
    }

    /**
     * Update the specified resource in storage.
     * Admin functionality - not part of current phase.
     */
    public function update()
    {
        // Admin functionality
    }

    /**
     * Remove the specified resource from storage.
     * Admin functionality - not part of current phase.
     */
    public function destroy()
    {
        // Admin functionality
    }
}
