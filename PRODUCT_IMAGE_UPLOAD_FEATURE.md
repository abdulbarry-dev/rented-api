# Product Image Upload Feature Documentation

## Overview

The Product Image Upload feature allows authenticated users to upload up to **5 images** per product listing. This feature supports creating new products with images, updating existing product images, and automatic cleanup of old images when products are updated or deleted.

---

## Features

### ✅ Implemented Capabilities

1. **Multiple Image Uploads** (up to 5 images per product)
2. **Thumbnail Upload** (single image for product preview)
3. **Image Validation** (type, size, count)
4. **Automatic File Storage** (Laravel filesystem with public disk)
5. **URL Generation** (full public URLs returned in API responses)
6. **Automatic Cleanup** (old images deleted on update/delete)
7. **Clean Architecture** (Controller → Service → Repository → Model)
8. **PSR-12 Compliant** (Laravel Pint formatted)

---

## Technical Specifications

### Storage

- **Disk**: `public`
- **Thumbnail Directory**: `storage/app/public/products/thumbnails/`
- **Images Directory**: `storage/app/public/products/images/`
- **Public URL**: `http://localhost:8000/storage/products/...`

### Validation Rules

#### Thumbnail
- **Type**: Image file
- **Formats**: jpeg, jpg, png, webp
- **Max Size**: 2MB (2048KB)
- **Required**: No (optional)

#### Images Array
- **Type**: Array of image files
- **Max Count**: 5 images
- **Formats**: jpeg, jpg, png, webp
- **Max Size per Image**: 2MB (2048KB)
- **Required**: No (optional)

---

## API Endpoints

### 1. Create Product with Images

**Endpoint**: `POST /api/v1/products`

**Authentication**: Required (Bearer Token)

**Authorization**: User must be verified

**Content-Type**: `multipart/form-data`

**Request Body**:

```
category_id: 1 (required, integer, must exist in categories table)
title: "Professional Camera" (required, string, max 255 chars)
description: "High-quality DSLR camera..." (required, string, max 5000 chars)
price_per_day: 50.00 (required, numeric, min 1, max 999999.99)
is_for_sale: true (optional, boolean)
sale_price: 500.00 (optional, numeric, required if is_for_sale=true)
is_available: true (optional, boolean, default true)
thumbnail: [file] (optional, image, max 2MB)
images[]: [file] (optional, array of images, max 5 images)
images[]: [file]
images[]: [file]
```

**Success Response** (201 Created):

```json
{
  "message": "Product created successfully",
  "data": {
    "id": 1,
    "title": "Professional Camera",
    "description": "High-quality DSLR camera for professional photography",
    "price_per_day": 50.0,
    "is_for_sale": true,
    "sale_price": 500.0,
    "is_available": true,
    "thumbnail_url": "http://localhost:8000/storage/products/thumbnails/abc123.jpg",
    "image_urls": [
      "http://localhost:8000/storage/products/images/def456.jpg",
      "http://localhost:8000/storage/products/images/ghi789.jpg",
      "http://localhost:8000/storage/products/images/jkl012.jpg"
    ],
    "category": {
      "id": 1,
      "name": "Electronics"
    },
    "created_at": "2025-12-04T14:30:00Z",
    "updated_at": "2025-12-04T14:30:00Z"
  }
}
```

**Error Response** (422 Unprocessable Entity):

```json
{
  "message": "Validation failed.",
  "errors": {
    "images": ["You can upload a maximum of 5 images."],
    "images.0": ["Each image must not exceed 2MB."],
    "thumbnail": ["Thumbnail must be an image."]
  }
}
```

---

### 2. Update Product Images

**Endpoint**: `PUT /api/v1/products/{id}`

**Authentication**: Required (Bearer Token)

**Authorization**: User must be the product owner

**Content-Type**: `multipart/form-data`

**Note**: When updating images, **all old images are deleted** and replaced with the new ones.

**Request Body** (all fields optional):

```
title: "Updated Camera" (optional, string, max 255 chars)
description: "Updated description" (optional, string, max 5000 chars)
price_per_day: 60.00 (optional, numeric, min 1)
thumbnail: [file] (optional, replaces old thumbnail)
images[]: [file] (optional, replaces all old images, max 5)
images[]: [file]
```

**Success Response** (200 OK):

```json
{
  "message": "Product updated successfully",
  "data": {
    "id": 1,
    "title": "Updated Camera",
    "description": "Updated description",
    "price_per_day": 60.0,
    "is_for_sale": true,
    "sale_price": 500.0,
    "is_available": true,
    "thumbnail_url": "http://localhost:8000/storage/products/thumbnails/new123.jpg",
    "image_urls": [
      "http://localhost:8000/storage/products/images/new456.jpg",
      "http://localhost:8000/storage/products/images/new789.jpg"
    ],
    "category": {
      "id": 1,
      "name": "Electronics"
    },
    "created_at": "2025-12-04T14:30:00Z",
    "updated_at": "2025-12-04T15:00:00Z"
  }
}
```

---

### 3. Get Product Details

**Endpoint**: `GET /api/v1/products/{id}`

**Authentication**: Not required (public endpoint)

**Response** (200 OK):

```json
{
  "data": {
    "id": 1,
    "title": "Professional Camera",
    "description": "High-quality DSLR camera",
    "price_per_day": 50.0,
    "is_for_sale": true,
    "sale_price": 500.0,
    "is_available": true,
    "thumbnail_url": "http://localhost:8000/storage/products/thumbnails/abc123.jpg",
    "image_urls": [
      "http://localhost:8000/storage/products/images/def456.jpg",
      "http://localhost:8000/storage/products/images/ghi789.jpg"
    ],
    "category": {
      "id": 1,
      "name": "Electronics"
    },
    "created_at": "2025-12-04T14:30:00Z",
    "updated_at": "2025-12-04T14:30:00Z"
  }
}
```

---

### 4. Delete Product

**Endpoint**: `DELETE /api/v1/products/{id}`

**Authentication**: Required (Bearer Token)

**Authorization**: User must be the product owner

**Note**: Deleting a product automatically deletes all associated images (thumbnail and all images).

**Success Response** (200 OK):

```json
{
  "message": "Product deleted successfully"
}
```

---

## Code Implementation

### 1. Form Request Validation

#### `app/Http/Requests/StoreProductRequest.php`

```php
public function rules(): array
{
    return [
        'category_id' => 'required|integer|exists:categories,id',
        'title' => 'required|string|max:255',
        'description' => 'required|string|max:5000',
        'price_per_day' => 'required|numeric|min:1|max:999999.99',
        'is_for_sale' => 'nullable|boolean',
        'sale_price' => 'nullable|numeric|min:1|max:999999.99|required_if:is_for_sale,true',
        'is_available' => 'nullable|boolean',
        'thumbnail' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
        'images' => 'nullable|array|max:5',
        'images.*' => 'image|mimes:jpeg,jpg,png,webp|max:2048',
    ];
}

public function messages(): array
{
    return [
        'thumbnail.image' => 'Thumbnail must be an image.',
        'thumbnail.max' => 'Thumbnail must not exceed 2MB.',
        'images.max' => 'You can upload a maximum of 5 images.',
        'images.*.image' => 'All uploaded files must be images.',
        'images.*.max' => 'Each image must not exceed 2MB.',
    ];
}
```

---

### 2. Product Service

#### `app/Services/ProductService.php`

Key methods:

```php
public function createProduct(User $user, array $data): Product
{
    // Handle thumbnail upload
    if (isset($data['thumbnail'])) {
        $data['thumbnail'] = $this->uploadFile($data['thumbnail'], 'products/thumbnails');
    }

    // Handle multiple images upload
    if (isset($data['images']) && is_array($data['images'])) {
        $imagePaths = [];
        foreach ($data['images'] as $image) {
            $imagePaths[] = $this->uploadFile($image, 'products/images');
        }
        $data['images'] = $imagePaths;
    }

    $data['user_id'] = $user->id;
    $data['is_available'] = $data['is_available'] ?? true;
    $data['is_for_sale'] = $data['is_for_sale'] ?? false;

    $product = $this->repository->create($data);
    $this->clearProductCaches();

    return $product;
}

public function updateProduct(Product $product, array $data): Product
{
    // Handle thumbnail upload
    if (isset($data['thumbnail'])) {
        // Delete old thumbnail
        if ($product->thumbnail) {
            Storage::disk('public')->delete($product->thumbnail);
        }
        $data['thumbnail'] = $this->uploadFile($data['thumbnail'], 'products/thumbnails');
    }

    // Handle multiple images upload
    if (isset($data['images']) && is_array($data['images'])) {
        // Delete old images
        if ($product->images && is_array($product->images)) {
            foreach ($product->images as $oldImage) {
                Storage::disk('public')->delete($oldImage);
            }
        }

        $imagePaths = [];
        foreach ($data['images'] as $image) {
            $imagePaths[] = $this->uploadFile($image, 'products/images');
        }
        $data['images'] = $imagePaths;
    }

    $this->repository->update($product, $data);
    $this->clearProductCaches($product->id);

    return $product->fresh();
}

public function deleteProduct(Product $product): bool
{
    // Delete associated files
    if ($product->thumbnail) {
        Storage::disk('public')->delete($product->thumbnail);
    }

    if ($product->images && is_array($product->images)) {
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image);
        }
    }

    $result = $this->repository->delete($product);
    $this->clearProductCaches($product->id);

    return $result;
}

private function uploadFile(UploadedFile $file, string $directory): string
{
    return $file->store($directory, 'public');
}
```

---

### 3. Product Model

#### `app/Models/Product.php`

```php
protected $fillable = [
    'user_id',
    'category_id',
    'title',
    'description',
    'price_per_day',
    'is_for_sale',
    'sale_price',
    'is_available',
    'thumbnail',
    'images',
];

protected $casts = [
    'price_per_day' => 'decimal:2',
    'sale_price' => 'decimal:2',
    'is_for_sale' => 'boolean',
    'is_available' => 'boolean',
    'images' => 'array',
];

/**
 * Get the full URL for the thumbnail.
 */
public function getThumbnailUrlAttribute(): ?string
{
    if (!$this->thumbnail) {
        return null;
    }

    return asset('storage/' . $this->thumbnail);
}

/**
 * Get full URLs for all product images.
 */
public function getImageUrlsAttribute(): array
{
    if (!$this->images || !is_array($this->images)) {
        return [];
    }

    return array_map(function ($imagePath) {
        return asset('storage/' . $imagePath);
    }, $this->images);
}
```

---

### 4. Product Resource

#### `app/Http/Resources/ProductResource.php`

```php
public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'title' => $this->title,
        'description' => $this->description,
        'price_per_day' => (float) $this->price_per_day,
        'is_for_sale' => $this->is_for_sale,
        'sale_price' => $this->sale_price ? (float) $this->sale_price : null,
        'is_available' => $this->is_available,
        'thumbnail_url' => $this->thumbnail_url,
        'image_urls' => $this->image_urls,
        'category' => new CategoryResource($this->whenLoaded('category')),
        'created_at' => $this->created_at?->toIso8601String(),
        'updated_at' => $this->updated_at?->toIso8601String(),
    ];
}
```

---

### 5. Product Controller

#### `app/Http/Controllers/Api/ProductController.php`

```php
public function store(StoreProductRequest $request): JsonResponse
{
    $this->authorize('create', Product::class);

    $product = $this->service->createProduct(
        $request->user(),
        $request->validated()
    );

    return response()->json([
        'message' => 'Product created successfully',
        'data' => new ProductResource($product),
    ], 201);
}

public function update(UpdateProductRequest $request, int $id): JsonResponse
{
    $product = $this->service->getProductById($id);

    if (!$product) {
        return response()->json([
            'message' => 'Product not found',
        ], 404);
    }

    $this->authorize('update', $product);

    $updated = $this->service->updateProduct($product, $request->validated());

    return response()->json([
        'message' => 'Product updated successfully',
        'data' => new ProductResource($updated),
    ]);
}

public function destroy(int $id): JsonResponse
{
    $product = $this->service->getProductById($id);

    if (!$product) {
        return response()->json([
            'message' => 'Product not found',
        ], 404);
    }

    $this->authorize('delete', $product);

    $this->service->deleteProduct($product);

    return response()->json([
        'message' => 'Product deleted successfully',
    ]);
}
```

---

## Testing Examples

### cURL Examples

#### Create Product with Images

```bash
curl -X POST http://localhost:8000/api/v1/products \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json" \
  -F "category_id=1" \
  -F "title=Professional Camera" \
  -F "description=High-quality DSLR camera for professional photography" \
  -F "price_per_day=50.00" \
  -F "is_for_sale=true" \
  -F "sale_price=500.00" \
  -F "thumbnail=@/path/to/thumbnail.jpg" \
  -F "images[]=@/path/to/image1.jpg" \
  -F "images[]=@/path/to/image2.jpg" \
  -F "images[]=@/path/to/image3.jpg"
```

#### Update Product Images

```bash
curl -X PUT http://localhost:8000/api/v1/products/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json" \
  -F "title=Updated Camera Title" \
  -F "thumbnail=@/path/to/new-thumbnail.jpg" \
  -F "images[]=@/path/to/new-image1.jpg" \
  -F "images[]=@/path/to/new-image2.jpg"
```

#### Get Product Details

```bash
curl -X GET http://localhost:8000/api/v1/products/1 \
  -H "Accept: application/json"
```

#### Delete Product

```bash
curl -X DELETE http://localhost:8000/api/v1/products/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

---

### iOS Swift Example

```swift
import UIKit

func createProductWithImages(
    token: String,
    categoryId: Int,
    title: String,
    description: String,
    pricePerDay: Double,
    thumbnail: UIImage?,
    images: [UIImage]
) {
    let url = URL(string: "http://localhost:8000/api/v1/products")!
    var request = URLRequest(url: url)
    request.httpMethod = "POST"
    request.setValue("Bearer \(token)", forHTTPHeaderField: "Authorization")
    
    let boundary = UUID().uuidString
    request.setValue("multipart/form-data; boundary=\(boundary)", forHTTPHeaderField: "Content-Type")
    
    var body = Data()
    
    // Add text fields
    body.append("--\(boundary)\r\n".data(using: .utf8)!)
    body.append("Content-Disposition: form-data; name=\"category_id\"\r\n\r\n".data(using: .utf8)!)
    body.append("\(categoryId)\r\n".data(using: .utf8)!)
    
    body.append("--\(boundary)\r\n".data(using: .utf8)!)
    body.append("Content-Disposition: form-data; name=\"title\"\r\n\r\n".data(using: .utf8)!)
    body.append("\(title)\r\n".data(using: .utf8)!)
    
    body.append("--\(boundary)\r\n".data(using: .utf8)!)
    body.append("Content-Disposition: form-data; name=\"description\"\r\n\r\n".data(using: .utf8)!)
    body.append("\(description)\r\n".data(using: .utf8)!)
    
    body.append("--\(boundary)\r\n".data(using: .utf8)!)
    body.append("Content-Disposition: form-data; name=\"price_per_day\"\r\n\r\n".data(using: .utf8)!)
    body.append("\(pricePerDay)\r\n".data(using: .utf8)!)
    
    // Add thumbnail
    if let thumbnail = thumbnail, let imageData = thumbnail.jpegData(compressionQuality: 0.8) {
        body.append("--\(boundary)\r\n".data(using: .utf8)!)
        body.append("Content-Disposition: form-data; name=\"thumbnail\"; filename=\"thumbnail.jpg\"\r\n".data(using: .utf8)!)
        body.append("Content-Type: image/jpeg\r\n\r\n".data(using: .utf8)!)
        body.append(imageData)
        body.append("\r\n".data(using: .utf8)!)
    }
    
    // Add images (max 5)
    for (index, image) in images.prefix(5).enumerated() {
        if let imageData = image.jpegData(compressionQuality: 0.8) {
            body.append("--\(boundary)\r\n".data(using: .utf8)!)
            body.append("Content-Disposition: form-data; name=\"images[]\"; filename=\"image\(index).jpg\"\r\n".data(using: .utf8)!)
            body.append("Content-Type: image/jpeg\r\n\r\n".data(using: .utf8)!)
            body.append(imageData)
            body.append("\r\n".data(using: .utf8)!)
        }
    }
    
    body.append("--\(boundary)--\r\n".data(using: .utf8)!)
    
    request.httpBody = body
    
    URLSession.shared.dataTask(with: request) { data, response, error in
        guard let data = data, error == nil else {
            print("Error: \(error?.localizedDescription ?? "Unknown error")")
            return
        }
        
        if let httpResponse = response as? HTTPURLResponse,
           httpResponse.statusCode == 201 {
            print("Product created successfully!")
            if let json = try? JSONSerialization.jsonObject(with: data) {
                print(json)
            }
        } else {
            print("Failed to create product")
        }
    }.resume()
}
```

---

### Android Kotlin Example

```kotlin
import okhttp3.*
import okhttp3.MediaType.Companion.toMediaTypeOrNull
import okhttp3.RequestBody.Companion.asRequestBody
import java.io.File

fun createProductWithImages(
    token: String,
    categoryId: Int,
    title: String,
    description: String,
    pricePerDay: Double,
    thumbnail: File?,
    images: List<File>
) {
    val client = OkHttpClient()
    
    val requestBody = MultipartBody.Builder()
        .setType(MultipartBody.FORM)
        .addFormDataPart("category_id", categoryId.toString())
        .addFormDataPart("title", title)
        .addFormDataPart("description", description)
        .addFormDataPart("price_per_day", pricePerDay.toString())
    
    // Add thumbnail if provided
    thumbnail?.let {
        requestBody.addFormDataPart(
            "thumbnail",
            it.name,
            it.asRequestBody("image/jpeg".toMediaTypeOrNull())
        )
    }
    
    // Add images (max 5)
    images.take(5).forEach { imageFile ->
        requestBody.addFormDataPart(
            "images[]",
            imageFile.name,
            imageFile.asRequestBody("image/jpeg".toMediaTypeOrNull())
        )
    }
    
    val request = Request.Builder()
        .url("http://localhost:8000/api/v1/products")
        .addHeader("Authorization", "Bearer $token")
        .addHeader("Accept", "application/json")
        .post(requestBody.build())
        .build()
    
    client.newCall(request).execute().use { response ->
        if (response.isSuccessful) {
            println("Product created successfully!")
            println(response.body?.string())
        } else {
            println("Failed to create product: ${response.code}")
            println(response.body?.string())
        }
    }
}
```

---

## Database Schema

### Products Table

```sql
CREATE TABLE products (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    category_id BIGINT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price_per_day DECIMAL(10, 2) NOT NULL,
    is_for_sale BOOLEAN DEFAULT FALSE,
    sale_price DECIMAL(10, 2),
    is_available BOOLEAN DEFAULT TRUE,
    thumbnail VARCHAR(255),
    images TEXT,  -- JSON array of image paths
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
);
```

**Note**: The `images` column stores a JSON array of file paths:

```json
[
  "products/images/abc123.jpg",
  "products/images/def456.jpg",
  "products/images/ghi789.jpg"
]
```

---

## Error Handling

### Validation Errors

```json
{
  "message": "Validation failed.",
  "errors": {
    "images": ["You can upload a maximum of 5 images."],
    "images.0": ["All uploaded files must be images."],
    "images.2": ["Each image must not exceed 2MB."],
    "thumbnail": ["Thumbnail must be an image."]
  }
}
```

### Authorization Errors

```json
{
  "message": "This action is unauthorized."
}
```

### Not Found Errors

```json
{
  "message": "Product not found"
}
```

---

## Best Practices

### 1. Image Optimization

Before uploading, optimize images:
- **Compress images** to reduce file size (use tools like TinyPNG, ImageOptim)
- **Resize large images** to reasonable dimensions (e.g., 1920x1080 max)
- **Use appropriate formats**: JPEG for photos, PNG for graphics, WebP for modern browsers

### 2. Upload Limits

- Maximum **5 images** per product
- Maximum **2MB** per image
- Supported formats: **JPEG, JPG, PNG, WebP**

### 3. Update Behavior

When updating product images:
- **All old images are deleted** from storage
- **New images replace old ones** completely
- To keep existing images, **don't send the images field** in the update request

### 4. Storage Symlink

Ensure the storage symbolic link is created:

```bash
php artisan storage:link
```

This creates a symbolic link from `public/storage` to `storage/app/public`, making uploaded files accessible via public URLs.

---

## Security Considerations

### 1. Authentication & Authorization

- **Create/Update/Delete**: Requires authentication (Sanctum Bearer token)
- **Update/Delete**: Only the product owner can modify their products
- **Read**: Public access (no authentication required)

### 2. File Validation

- All uploaded files are validated for:
  - File type (must be image)
  - MIME type (jpeg, jpg, png, webp only)
  - File size (max 2MB per image)
  - Array count (max 5 images)

### 3. Automatic Cleanup

- Old images are automatically deleted when:
  - Product is updated with new images
  - Product is deleted

---

## Troubleshooting

### Issue: Images not accessible via URL

**Solution**: Ensure storage symlink is created:

```bash
php artisan storage:link
```

### Issue: "Maximum upload file size exceeded"

**Solution**: Increase PHP upload limits in `php.ini`:

```ini
upload_max_filesize = 10M
post_max_size = 10M
```

### Issue: "You can upload a maximum of 5 images"

**Solution**: Reduce the number of images to 5 or fewer.

### Issue: "Each image must not exceed 2MB"

**Solution**: Compress images before uploading or use smaller images.

---

## Summary

The Product Image Upload feature is **fully implemented** with:

✅ **5-image limit** enforced through validation  
✅ **Files saved** using Laravel filesystem in public directory  
✅ **Only file paths stored** in database (`images` column as JSON array)  
✅ **Full public URLs** returned in API responses  
✅ **Automatic cleanup** of old images  
✅ **Clean architecture** following Laravel best practices  
✅ **PSR-12 compliant** code  

The feature is production-ready and follows the same clean architecture pattern as the avatar upload feature.
