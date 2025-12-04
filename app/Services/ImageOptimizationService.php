<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageOptimizationService
{
    /**
     * Optimize and store image with compression.
     */
    public function optimizeAndStore(
        UploadedFile $file,
        string $directory,
        int $maxWidth = 1920,
        int $quality = 85
    ): string {
        // Generate unique filename
        $filename = time().'_'.uniqid().'.jpg';
        $path = $directory.'/'.$filename;

        // Get image info
        $imageInfo = getimagesize($file->getRealPath());
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $mimeType = $imageInfo['mime'];

        // Create image resource based on mime type
        $sourceImage = match ($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($file->getRealPath()),
            'image/png' => imagecreatefrompng($file->getRealPath()),
            'image/gif' => imagecreatefromgif($file->getRealPath()),
            default => throw new \Exception('Unsupported image type'),
        };

        // Calculate new dimensions if needed
        if ($width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = (int) ($height * ($maxWidth / $width));
        } else {
            $newWidth = $width;
            $newHeight = $height;
        }

        // Create new image with optimized dimensions
        $optimizedImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled(
            $optimizedImage,
            $sourceImage,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $width, $height
        );

        // Save to temporary file with compression
        $tempPath = sys_get_temp_dir().'/'.$filename;
        imagejpeg($optimizedImage, $tempPath, $quality);

        // Store optimized image in private storage
        Storage::disk('private')->put($path, file_get_contents($tempPath));

        // Clean up
        imagedestroy($sourceImage);
        imagedestroy($optimizedImage);
        unlink($tempPath);

        return $path;
    }

    /**
     * Delete image from storage.
     */
    public function delete(string $path, string $disk = 'private'): bool
    {
        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }

    /**
     * Delete multiple images from storage.
     */
    public function deleteMultiple(array $paths): void
    {
        foreach ($paths as $path) {
            if ($path) {
                $this->delete($path);
            }
        }
    }
}
