# Storage Setup Guide

## Public Storage for Product Images

Product images are stored in the **public** storage disk and are **publicly accessible** without authentication.

### Storage Configuration

- **Disk**: `public` (located at `storage/app/public`)
- **Access**: Public - no authentication required
- **URL Format**: `http://your-domain.com/storage/products/images/filename.jpg`

### Setup Requirements

#### 1. Create Storage Symlink

To make the public storage accessible via web, create a symbolic link:

```bash
php artisan storage:link
```

This creates a symlink from `public/storage` to `storage/app/public`.

#### 2. Verify Symlink

Check if the symlink exists:

```bash
ls -la public/ | grep storage
```

Expected output:
```
lrwxrwxrwx storage -> /path/to/storage/app/public
```

### Docker Setup

For Docker environments, the symlink is automatically created in the `docker-entrypoint.sh`:

```bash
php artisan storage:link
```

### Directory Structure

```
storage/
├── app/
│   ├── public/              # Publicly accessible files
│   │   └── products/
│   │       └── images/      # Product images (PUBLIC)
│   └── private/             # Private files (verification docs)
│       └── verifications/
│           ├── national-ids/ # ID documents (PRIVATE)
│           └── selfies/      # Selfie photos (PRIVATE)
```

### Access Control

| Directory | Visibility | Authentication | Rate Limit |
|-----------|-----------|----------------|------------|
| `public/products/images/` | Public | None | None |
| `private/verifications/` | Private | Required (owner only) | 60/min |

### Image URLs

Product images are automatically converted to full URLs:

```php
// Model accessor
$product->image_urls; // Returns array of full URLs

// Example output
[
    "http://localhost:8000/storage/products/images/1733328045_abc123.jpg",
    "http://localhost:8000/storage/products/images/1733328045_def456.jpg"
]
```

### API Response

Product endpoints (`GET /products` and `GET /products/{id}`) return:

```json
{
  "data": {
    "id": 1,
    "title": "Canon EOS R5",
    "image_urls": [
      "http://localhost:8000/storage/products/images/image1.jpg",
      "http://localhost:8000/storage/products/images/image2.jpg"
    ]
  }
}
```

These URLs are **directly accessible** - no authentication or special headers required.

### Flutter/Mobile Implementation

```dart
// Simply use the URL directly with any image widget
CachedNetworkImage(
  imageUrl: product['image_urls'][0],
  // No authentication headers needed!
  fit: BoxFit.cover,
)
```

### Important Notes

1. **Product Images = Public**: Anyone can view product images using the URLs
2. **Verification Images = Private**: Only owners can access their verification documents via API endpoint
3. **No Middleware**: Product image URLs bypass all authentication middleware
4. **CDN Ready**: These URLs can be easily served via CDN for better performance
5. **Storage Link Required**: Always run `php artisan storage:link` after deployment

### Troubleshooting

#### Images Not Loading

1. Check symlink exists: `ls -la public/storage`
2. Check file permissions: `ls -la storage/app/public/products/images/`
3. Verify APP_URL in `.env` matches your domain
4. Clear cache: `php artisan cache:clear`

#### 404 on Image URLs

- Run `php artisan storage:link`
- Check nginx/apache configuration serves static files from `public/storage/`
- Verify files exist: `ls storage/app/public/products/images/`
