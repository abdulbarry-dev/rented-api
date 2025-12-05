<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ImageUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ImageUploadController extends Controller
{
    public function __construct(
        private ImageUploadService $imageUploadService
    ) {}

    /**
     * Upload a single image (avatar, thumbnail, etc).
     * Supports both file upload and base64 encoded images.
     */
    public function uploadSingle(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required',
            'type' => 'required|in:avatar,product_thumbnail,product_image,general',
            'base64' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $file = $request->input('base64') === true
                ? $request->input('image')
                : $request->file('image');

            $path = match ($request->input('type')) {
                'avatar' => $this->imageUploadService->uploadAvatar($file),
                'product_thumbnail' => $this->imageUploadService->uploadProductThumbnail($file),
                'product_image' => $this->imageUploadService->uploadProductImages([$file])[0],
                'general' => $this->imageUploadService->uploadImage($file, 'images')['original'],
                default => throw new \Exception('Invalid image type'),
            };

            return response()->json([
                'message' => 'Image uploaded successfully',
                'data' => [
                    'path' => $path,
                    'url' => $this->imageUploadService->getUrl($path),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Image upload failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload multiple images.
     * Supports both file uploads and base64 encoded images.
     */
    public function uploadMultiple(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array|min:1|max:10',
            'images.*' => $request->input('base64') ? 'required|string' : 'required|file|image|max:5120',
            'type' => 'required|in:product_images,gallery,general',
            'base64' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $files = $request->input('base64') === true
                ? $request->input('images')
                : $request->file('images');

            $paths = match ($request->input('type')) {
                'product_images' => $this->imageUploadService->uploadProductImages($files),
                'gallery', 'general' => array_map(
                    fn ($file) => $this->imageUploadService->uploadImage($file, 'gallery')['original'],
                    $files
                ),
                default => throw new \Exception('Invalid image type'),
            };

            $urls = array_map(
                fn ($path) => $this->imageUploadService->getUrl($path),
                $paths
            );

            return response()->json([
                'message' => count($paths).' images uploaded successfully',
                'data' => [
                    'paths' => $paths,
                    'urls' => $urls,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Image upload failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an image.
     */
    public function delete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'path' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $this->imageUploadService->delete($request->input('path'));

            return response()->json([
                'message' => 'Image deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Image deletion failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload avatar (dedicated endpoint).
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required',
            'base64' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $file = $request->input('base64') === true
                ? $request->input('avatar')
                : $request->file('avatar');

            $path = $this->imageUploadService->uploadAvatar($file);

            return response()->json([
                'message' => 'Avatar uploaded successfully',
                'data' => [
                    'path' => $path,
                    'url' => $this->imageUploadService->getUrl($path),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Avatar upload failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
