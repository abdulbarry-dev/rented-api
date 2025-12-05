<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageUploadService
{
    private ImageManager $imageManager;

    public function __construct()
    {
        // Use GD driver for image manipulation
        $this->imageManager = new ImageManager(new Driver);
    }

    /**
     * Upload and optimize image with multiple size variants.
     */
    public function uploadImage(
        UploadedFile|string $file,
        string $directory = 'images',
        array $sizes = ['original', 'large', 'medium', 'thumbnail'],
        string $disk = 'public'
    ): array {
        $filename = $this->generateUniqueFilename();

        // Handle base64 image
        if (is_string($file) && str_starts_with($file, 'data:image')) {
            $file = $this->base64ToUploadedFile($file);
        }

        $paths = [];

        // Define size configurations
        $sizeConfig = [
            'original' => ['width' => null, 'quality' => 90],
            'large' => ['width' => 1920, 'quality' => 85],
            'medium' => ['width' => 800, 'quality' => 80],
            'thumbnail' => ['width' => 300, 'quality' => 75],
        ];

        foreach ($sizes as $size) {
            if (! isset($sizeConfig[$size])) {
                continue;
            }

            $config = $sizeConfig[$size];
            $sizeSuffix = $size === 'original' ? '' : "_{$size}";
            $path = "{$directory}/{$filename}{$sizeSuffix}.jpg";

            $image = $this->imageManager->read($file->getRealPath());

            // Resize if width is specified
            if ($config['width']) {
                $image->scale(width: $config['width']);
            }

            // Convert to JPEG and save
            $encoded = $image->toJpeg($config['quality']);

            // Store the image
            Storage::disk($disk)->put($path, (string) $encoded);

            $paths[$size] = $path;
        }

        return $paths;
    }

    /**
     * Upload multiple images.
     */
    public function uploadMultiple(
        array $files,
        string $directory = 'images',
        array $sizes = ['original', 'medium', 'thumbnail'],
        string $disk = 'public'
    ): array {
        $results = [];

        foreach ($files as $file) {
            $results[] = $this->uploadImage($file, $directory, $sizes, $disk);
        }

        return $results;
    }

    /**
     * Upload avatar with circular crop option.
     */
    public function uploadAvatar(
        UploadedFile|string $file,
        string $directory = 'avatars',
        int $size = 400,
        string $disk = 'public'
    ): string {
        $filename = $this->generateUniqueFilename();
        $path = "{$directory}/{$filename}.jpg";

        // Handle base64 image
        if (is_string($file) && str_starts_with($file, 'data:image')) {
            $file = $this->base64ToUploadedFile($file);
        }

        $image = $this->imageManager->read($file->getRealPath());

        // Cover to square (crop from center)
        $image->cover($size, $size);

        // Convert to JPEG and save
        $encoded = $image->toJpeg(85);

        // Store the image
        Storage::disk($disk)->put($path, (string) $encoded);

        return $path;
    }

    /**
     * Upload product thumbnail with specific dimensions.
     */
    public function uploadProductThumbnail(
        UploadedFile|string $file,
        string $disk = 'public'
    ): string {
        $filename = $this->generateUniqueFilename();
        $path = "products/thumbnails/{$filename}.jpg";

        // Handle base64 image
        if (is_string($file) && str_starts_with($file, 'data:image')) {
            $file = $this->base64ToUploadedFile($file);
        }

        $image = $this->imageManager->read($file->getRealPath());

        // Scale to 800px width maintaining aspect ratio
        $image->scale(width: 800);

        // Convert to JPEG and save
        $encoded = $image->toJpeg(85);

        Storage::disk($disk)->put($path, (string) $encoded);

        return $path;
    }

    /**
     * Upload product gallery images.
     */
    public function uploadProductImages(
        array $files,
        string $disk = 'public'
    ): array {
        $paths = [];

        foreach ($files as $file) {
            $filename = $this->generateUniqueFilename();
            $path = "products/images/{$filename}.jpg";

            // Handle base64 image
            if (is_string($file) && str_starts_with($file, 'data:image')) {
                $file = $this->base64ToUploadedFile($file);
            }

            $image = $this->imageManager->read($file->getRealPath());

            // Scale to max 1920px width
            $image->scale(width: 1920);

            // Convert to JPEG and save
            $encoded = $image->toJpeg(85);

            Storage::disk($disk)->put($path, (string) $encoded);

            $paths[] = $path;
        }

        return $paths;
    }

    /**
     * Delete image(s) from storage.
     */
    public function delete(string|array $paths, string $disk = 'public'): void
    {
        $paths = is_array($paths) ? $paths : [$paths];

        foreach ($paths as $path) {
            if ($path && Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        }
    }

    /**
     * Delete all size variants of an image.
     */
    public function deleteVariants(string $basePath, string $disk = 'public'): void
    {
        $directory = dirname($basePath);
        $filename = pathinfo($basePath, PATHINFO_FILENAME);

        $variants = [
            $basePath,
            "{$directory}/{$filename}_large.jpg",
            "{$directory}/{$filename}_medium.jpg",
            "{$directory}/{$filename}_thumbnail.jpg",
        ];

        $this->delete($variants, $disk);
    }

    /**
     * Convert base64 string to UploadedFile.
     */
    private function base64ToUploadedFile(string $base64String): UploadedFile
    {
        // Extract the base64 encoded file data
        if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $type)) {
            $data = substr($base64String, strpos($base64String, ',') + 1);
            $type = strtolower($type[1]);

            $data = base64_decode($data);

            if ($data === false) {
                throw new \Exception('Base64 decode failed');
            }

            $tmpFile = tempnam(sys_get_temp_dir(), 'img_');
            file_put_contents($tmpFile, $data);

            $mimeType = "image/{$type}";
            $originalName = uniqid().'.'.$type;

            return new UploadedFile(
                $tmpFile,
                $originalName,
                $mimeType,
                null,
                true
            );
        }

        throw new \Exception('Invalid base64 image format');
    }

    /**
     * Generate unique filename.
     */
    private function generateUniqueFilename(): string
    {
        return date('YmdHis').'_'.Str::random(16);
    }

    /**
     * Get image URL from path.
     */
    public function getUrl(string $path, string $disk = 'public'): string
    {
        return Storage::disk($disk)->url($path);
    }

    /**
     * Validate image file or base64 string.
     */
    public function validateImage(UploadedFile|string $file, int $maxSizeMB = 5): bool
    {
        if (is_string($file)) {
            // Validate base64 string
            if (! str_starts_with($file, 'data:image')) {
                return false;
            }

            // Extract and check size
            $data = substr($file, strpos($file, ',') + 1);
            $decodedSize = strlen(base64_decode($data));

            return $decodedSize <= ($maxSizeMB * 1024 * 1024);
        }

        // Validate UploadedFile
        if (! $file->isValid()) {
            return false;
        }

        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
        if (! in_array($file->getMimeType(), $allowedMimes)) {
            return false;
        }

        return $file->getSize() <= ($maxSizeMB * 1024 * 1024);
    }
}
