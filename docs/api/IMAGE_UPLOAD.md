# Image Upload API Documentation

## Overview
The Image Upload API provides seamless and optimized image upload functionality with support for both traditional file uploads and base64 encoded images. All images are automatically optimized, resized, and converted to JPEG format for consistent quality and performance.

## Features
- ✅ **Automatic Optimization**: All images are compressed and optimized
- ✅ **Multiple Formats**: Supports JPEG, PNG, WebP, GIF input formats
- ✅ **Base64 Support**: Upload images as base64 strings or files
- ✅ **Size Variants**: Generate multiple size variants (thumbnail, medium, large, original)
- ✅ **Smart Cropping**: Automatic cropping for avatars (square/circular)
- ✅ **Validation**: Built-in validation for file size and type
- ✅ **Secure Storage**: Files stored securely in Laravel's storage system

---

## Authentication
All image upload endpoints require authentication using Bearer token:
```
Authorization: Bearer {your-token-here}
```

---

## Endpoints

### 1. Upload Single Image

Upload a single image with type-specific processing.

**Endpoint:** `POST /api/v1/upload/image`

**Request Headers:**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data (for file upload)
Content-Type: application/json (for base64 upload)
```

**Request Body (File Upload):**
```
type: avatar|product_thumbnail|product_image|general (required)
image: file (required)
base64: false
```

**Request Body (Base64 Upload):**
```json
{
  "type": "avatar",
  "image": "data:image/jpeg;base64,/9j/4AAQSkZJRg...",
  "base64": true
}
```

**Image Types:**
- `avatar` - 400x400px square, optimized for profile pictures
- `product_thumbnail` - 800px width, maintains aspect ratio
- `product_image` - 1920px max width, high quality
- `general` - Original size with optimization

**Success Response (201):**
```json
{
  "message": "Image uploaded successfully",
  "data": {
    "path": "avatars/20251205120530_abc123def456.jpg",
    "url": "http://your-domain.com/storage/avatars/20251205120530_abc123def456.jpg"
  }
}
```

**Error Response (422):**
```json
{
  "message": "Validation failed",
  "errors": {
    "image": ["The image field is required."],
    "type": ["The type field is required."]
  }
}
```

**cURL Example (File):**
```bash
curl -X POST http://your-domain.com/api/v1/upload/image \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "type=avatar" \
  -F "image=@/path/to/image.jpg"
```

**cURL Example (Base64):**
```bash
curl -X POST http://your-domain.com/api/v1/upload/image \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "avatar",
    "image": "data:image/jpeg;base64,/9j/4AAQSkZJRg...",
    "base64": true
  }'
```

---

### 2. Upload Multiple Images

Upload multiple images at once (up to 10 images).

**Endpoint:** `POST /api/v1/upload/images`

**Request Headers:**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data (for file upload)
Content-Type: application/json (for base64 upload)
```

**Request Body (File Upload):**
```
type: product_images|gallery|general (required)
images[]: file (required, array of images)
base64: false
```

**Request Body (Base64 Upload):**
```json
{
  "type": "product_images",
  "images": [
    "data:image/jpeg;base64,/9j/4AAQSkZJRg...",
    "data:image/png;base64,iVBORw0KGgoAAAANSUh...",
    "data:image/jpeg;base64,/9j/4AAQSkZJRg..."
  ],
  "base64": true
}
```

**Validation Rules:**
- Minimum: 1 image
- Maximum: 10 images
- Max file size: 5MB per image
- Allowed formats: JPEG, PNG, WebP, GIF

**Success Response (201):**
```json
{
  "message": "3 images uploaded successfully",
  "data": {
    "paths": [
      "products/images/20251205120530_abc123def456.jpg",
      "products/images/20251205120531_def789ghi012.jpg",
      "products/images/20251205120532_jkl345mno678.jpg"
    ],
    "urls": [
      "http://your-domain.com/storage/products/images/20251205120530_abc123def456.jpg",
      "http://your-domain.com/storage/products/images/20251205120531_def789ghi012.jpg",
      "http://your-domain.com/storage/products/images/20251205120532_jkl345mno678.jpg"
    ]
  }
}
```

**Error Response (422):**
```json
{
  "message": "Validation failed",
  "errors": {
    "images": ["The images field must be an array."],
    "images.0": ["The images.0 must be an image."]
  }
}
```

**cURL Example (File):**
```bash
curl -X POST http://your-domain.com/api/v1/upload/images \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "type=product_images" \
  -F "images[]=@/path/to/image1.jpg" \
  -F "images[]=@/path/to/image2.jpg" \
  -F "images[]=@/path/to/image3.jpg"
```

**cURL Example (Base64):**
```bash
curl -X POST http://your-domain.com/api/v1/upload/images \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "product_images",
    "images": [
      "data:image/jpeg;base64,/9j/4AAQSkZJRg...",
      "data:image/jpeg;base64,/9j/4AAQSkZJRg..."
    ],
    "base64": true
  }'
```

---

### 3. Upload Avatar (Dedicated)

Dedicated endpoint for avatar uploads with automatic square cropping.

**Endpoint:** `POST /api/v1/upload/avatar`

**Request Headers:**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data (for file upload)
Content-Type: application/json (for base64 upload)
```

**Request Body (File Upload):**
```
avatar: file (required)
base64: false
```

**Request Body (Base64 Upload):**
```json
{
  "avatar": "data:image/jpeg;base64,/9j/4AAQSkZJRg...",
  "base64": true
}
```

**Processing:**
- Automatically cropped to 400x400px square
- Centered crop for best composition
- JPEG format with 85% quality
- Perfect for profile pictures

**Success Response (201):**
```json
{
  "message": "Avatar uploaded successfully",
  "data": {
    "path": "avatars/20251205120530_abc123def456.jpg",
    "url": "http://your-domain.com/storage/avatars/20251205120530_abc123def456.jpg"
  }
}
```

**cURL Example:**
```bash
curl -X POST http://your-domain.com/api/v1/upload/avatar \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "avatar=@/path/to/avatar.jpg"
```

---

### 4. Delete Image

Delete an uploaded image from storage.

**Endpoint:** `DELETE /api/v1/upload/image`

**Request Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "path": "avatars/20251205120530_abc123def456.jpg"
}
```

**Success Response (200):**
```json
{
  "message": "Image deleted successfully"
}
```

**Error Response (500):**
```json
{
  "message": "Image deletion failed",
  "error": "File not found"
}
```

**cURL Example:**
```bash
curl -X DELETE http://your-domain.com/api/v1/upload/image \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"path": "avatars/20251205120530_abc123def456.jpg"}'
```

---

## Image Size Guide

### Avatar Images
- **Dimensions:** 400x400px (square)
- **Format:** JPEG
- **Quality:** 85%
- **Max File Size:** 5MB (before processing)
- **Use Case:** User profile pictures

### Product Thumbnails
- **Dimensions:** 800px width (aspect ratio maintained)
- **Format:** JPEG
- **Quality:** 85%
- **Max File Size:** 5MB (before processing)
- **Use Case:** Product listing previews

### Product Gallery Images
- **Dimensions:** 1920px max width (aspect ratio maintained)
- **Format:** JPEG
- **Quality:** 85%
- **Max File Size:** 5MB (before processing)
- **Use Case:** Product detail page gallery

### General Images
- **Dimensions:** Original (with optimization)
- **Format:** JPEG
- **Quality:** 90%
- **Max File Size:** 5MB (before processing)
- **Use Case:** General purpose images

---

## Base64 Image Upload

### Advantages
- ✅ No need for multipart/form-data encoding
- ✅ Upload images directly from canvas/blob
- ✅ Easier integration with JavaScript/mobile apps
- ✅ Single JSON payload for image + metadata

### Format
Base64 images must include the data URI scheme:
```
data:image/jpeg;base64,/9j/4AAQSkZJRg...
data:image/png;base64,iVBORw0KGgoAAAANSUh...
data:image/webp;base64,UklGRvoPAABXRUJQVl...
```

### JavaScript Example
```javascript
// Convert file to base64
function fileToBase64(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = () => resolve(reader.result);
    reader.onerror = error => reject(error);
  });
}

// Upload avatar
const file = document.querySelector('#avatar-input').files[0];
const base64 = await fileToBase64(file);

fetch('http://your-domain.com/api/v1/upload/avatar', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer YOUR_TOKEN',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    avatar: base64,
    base64: true
  })
})
.then(response => response.json())
.then(data => console.log('Success:', data))
.catch(error => console.error('Error:', error));
```

---

## Error Codes

| Status Code | Description |
|-------------|-------------|
| 200 | Success |
| 201 | Image created successfully |
| 422 | Validation error (invalid input) |
| 500 | Server error (upload failed) |

---

## Best Practices

### For Frontend Developers
1. **Show preview before upload** - Let users see what they're uploading
2. **Validate file size client-side** - Prevent unnecessary large uploads
3. **Show upload progress** - Use FormData with XMLHttpRequest for progress tracking
4. **Handle errors gracefully** - Display user-friendly error messages
5. **Compress images client-side** - Reduce upload time (optional)

### For Mobile Developers
1. **Use base64 for camera captures** - Easier than file uploads
2. **Resize images before upload** - Save bandwidth and time
3. **Implement retry logic** - Handle network failures
4. **Cache uploaded URLs** - Avoid re-uploading same images

### For Backend Integration
1. **Store image paths in database** - Not the full URLs
2. **Use the returned path** - Don't reconstruct paths manually
3. **Delete old images** - When users update their avatars/images
4. **Lazy load images** - Use thumbnails for listings, full size for details

---

## Example Integration

### Creating a Product with Images

**Step 1: Upload images first**
```javascript
const formData = new FormData();
formData.append('type', 'product_images');
formData.append('images[]', thumbnailFile);
formData.append('images[]', image1File);
formData.append('images[]', image2File);

const uploadResponse = await fetch('/api/v1/upload/images', {
  method: 'POST',
  headers: { 'Authorization': 'Bearer ' + token },
  body: formData
});

const { data } = await uploadResponse.json();
const thumbnailPath = data.paths[0];
const imagePaths = data.paths.slice(1);
```

**Step 2: Create product with image paths**
```javascript
const productData = {
  category_id: 1,
  title: 'Professional Camera',
  description: 'High quality DSLR camera',
  price_per_day: 50.00,
  thumbnail: thumbnailPath,
  images: imagePaths
};

const productResponse = await fetch('/api/v1/products', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify(productData)
});
```

---

## Rate Limits

All image upload endpoints are subject to standard API rate limits:
- **Authenticated users:** 60 requests per minute
- **Guest users:** N/A (authentication required)

---

## Storage Location

Uploaded images are stored in the following directories:

```
storage/app/public/
├── avatars/              # User avatars (400x400)
├── products/
│   ├── thumbnails/       # Product thumbnails (800px wide)
│   └── images/           # Product gallery images (1920px wide)
├── gallery/              # General gallery images
└── images/               # General purpose images
```

---

## Notes

- All images are converted to JPEG format for consistency and optimal file size
- Original aspect ratios are maintained unless specifically cropped (avatars)
- Image quality is automatically optimized to balance quality and file size
- Maximum upload size is 5MB per image before processing
- After processing, images are typically 50-80% smaller than originals
- File names are automatically generated with timestamps and unique IDs
- Images are publicly accessible via the `/storage` URL prefix

---

## Support

For issues or questions regarding image uploads:
- Check error messages returned by the API
- Verify file format and size requirements
- Ensure proper authentication headers
- Review the cURL examples for proper request format
