<?php

/**
 * Quick script to create a placeholder laptop image for seeder testing
 */

$width = 1200;
$height = 800;
$outputPath = __DIR__ . '/../storage/app/seed-images/laptop.jpg';

// Create directory if it doesn't exist
$dir = dirname($outputPath);
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

// Create image
$image = imagecreatetruecolor($width, $height);

// Colors
$gray = imagecolorallocate($image, 100, 100, 100);
$white = imagecolorallocate($image, 255, 255, 255);
$darkGray = imagecolorallocate($image, 50, 50, 50);

// Fill background
imagefill($image, 0, 0, $gray);

// Draw laptop shape
$laptopWidth = 800;
$laptopHeight = 500;
$laptopX = ($width - $laptopWidth) / 2;
$laptopY = ($height - $laptopHeight) / 2;

// Laptop screen
imagefilledrectangle($image, $laptopX, $laptopY, $laptopX + $laptopWidth, $laptopY + $laptopHeight, $darkGray);
imagefilledrectangle($image, $laptopX + 20, $laptopY + 20, $laptopX + $laptopWidth - 20, $laptopY + $laptopHeight - 80, $white);

// Add text
$text = "LAPTOP TEST IMAGE";
$font = 5; // Built-in font
$textWidth = imagefontwidth($font) * strlen($text);
$textX = ($width - $textWidth) / 2;
$textY = $height / 2 - 20;

imagestring($image, $font, $textX, $textY, $text, $darkGray);

// Resolution text
$resText = "{$width}x{$height}";
$resTextWidth = imagefontwidth($font) * strlen($resText);
$resTextX = ($width - $resTextWidth) / 2;
$resTextY = $height / 2 + 20;

imagestring($image, $font, $resTextX, $resTextY, $resText, $darkGray);

// Save as JPEG
imagejpeg($image, $outputPath, 85);
imagedestroy($image);

echo "✅ Created test laptop image at: {$outputPath}\n";
echo "   Image size: {$width}x{$height}\n";
echo "   You can now run: php artisan db:seed --class=PrepareTestImages\n";
