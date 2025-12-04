# Avatar Upload Feature Documentation

## Overview

This feature allows authenticated users to upload, update, and delete their profile avatar image. The implementation follows Laravel best practices with proper validation, clean architecture, and secure file handling.

---

## Architecture

### Components

1. **UpdateAvatarRequest** - Form Request for validation
2. **AuthService** - Business logic for avatar operations
3. **AuthController** - API endpoints for avatar management
4. **User Model** - Avatar path storage and URL generation
5. **UserResource** - API response formatting
6. **Migration** - Database schema update

---

## API Endpoints

### 1. Upload/Update Avatar

**Endpoint:** `POST /api/v1/user/avatar`

**Authentication:** Required (Bearer Token)

**Content-Type:** `multipart/form-data`

**Request Body:**

```
avatar: file (required)
```

**Validation Rules:**

- Required: Yes
- Type: Image file
- Formats: JPEG, JPG, PNG
- Max Size: 2MB (2048 KB)
- Dimensions:
  - Minimum: 100x100 pixels
  - Maximum: 2000x2000 pixels

**Success Response (200 OK):**

```json
{
  "message": "Avatar updated successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "avatar_url": "http://localhost:8000/storage/avatars/xyz123.jpg",
    "verification_status": "pending",
    "created_at": "2025-12-03T14:30:45+00:00",
    "updated_at": "2025-12-03T15:45:20+00:00"
  }
}
```

**Error Response (422 Unprocessable Entity):**

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "avatar": [
      "Please select an avatar image.",
      "Avatar must be a JPEG, JPG, or PNG file.",
      "Avatar size must not exceed 2MB.",
      "Avatar dimensions must be between 100x100 and 2000x2000 pixels."
    ]
  }
}
```

**cURL Example:**

```bash
curl -X POST http://localhost:8000/api/v1/user/avatar \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json" \
  -F "avatar=@/path/to/image.jpg"
```

---

### 2. Delete Avatar

**Endpoint:** `DELETE /api/v1/user/avatar`

**Authentication:** Required (Bearer Token)

**Success Response (200 OK):**

```json
{
  "message": "Avatar deleted successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "avatar_url": null,
    "verification_status": "pending",
    "created_at": "2025-12-03T14:30:45+00:00",
    "updated_at": "2025-12-03T15:50:10+00:00"
  }
}
```

**cURL Example:**

```bash
curl -X DELETE http://localhost:8000/api/v1/user/avatar \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

---

## Implementation Details

### 1. Database Schema

**Migration:** `2025_12_04_123451_add_avatar_path_to_users_table.php`

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('avatar_path')->nullable()->after('email');
});
```

**Column Details:**

- Name: `avatar_path`
- Type: `string` (VARCHAR)
- Nullable: Yes
- Stores: Relative path to avatar file (e.g., `avatars/xyz123.jpg`)

---

### 2. User Model Updates

**New Fillable Field:**

```php
protected $fillable = [
    'name',
    'email',
    'password',
    'avatar_path',
    'verification_status',
    'verified_at',
];
```

**Avatar URL Accessor:**

```php
public function getAvatarUrlAttribute(): ?string
{
    if (!$this->avatar_path) {
        return null;
    }
    
    return asset('storage/' . $this->avatar_path);
}
```

This accessor automatically generates the full public URL for the avatar.

---

### 3. Service Layer

**AuthService Methods:**

#### updateAvatar()

```php
public function updateAvatar(User $user, UploadedFile $avatar): User
{
    // Delete old avatar if exists
    if ($user->avatar_path) {
        Storage::disk('public')->delete($user->avatar_path);
    }
    
    // Store new avatar
    $path = $avatar->store('avatars', 'public');
    
    // Update user avatar path
    $user->update(['avatar_path' => $path]);
    
    return $user->fresh();
}
```

**Features:**

- Automatically deletes old avatar before uploading new one
- Stores avatar in `storage/app/public/avatars/` directory
- Generates unique filename automatically
- Returns fresh user instance with updated data

#### deleteAvatar()

```php
public function deleteAvatar(User $user): User
{
    if ($user->avatar_path) {
        Storage::disk('public')->delete($user->avatar_path);
        $user->update(['avatar_path' => null]);
    }
    
    return $user->fresh();
}
```

**Features:**

- Checks if avatar exists before deletion
- Removes file from storage
- Sets `avatar_path` to null in database
- Returns fresh user instance

---

### 4. Validation

**UpdateAvatarRequest:**

```php
public function rules(): array
{
    return [
        'avatar' => [
            'required',
            'image',
            'mimes:jpeg,jpg,png',
            'max:2048', // 2MB
            'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000',
        ],
    ];
}
```

**Validation Details:**

| Rule | Purpose |
|------|---------|
| `required` | Avatar file must be provided |
| `image` | File must be a valid image |
| `mimes:jpeg,jpg,png` | Only JPEG and PNG formats allowed |
| `max:2048` | Maximum file size of 2MB |
| `dimensions` | Minimum 100x100px, Maximum 2000x2000px |

**Custom Error Messages:**

```php
public function messages(): array
{
    return [
        'avatar.required' => 'Please select an avatar image.',
        'avatar.image' => 'The file must be an image.',
        'avatar.mimes' => 'Avatar must be a JPEG, JPG, or PNG file.',
        'avatar.max' => 'Avatar size must not exceed 2MB.',
        'avatar.dimensions' => 'Avatar dimensions must be between 100x100 and 2000x2000 pixels.',
    ];
}
```

---

### 5. File Storage

**Storage Configuration:**

- **Disk:** `public`
- **Driver:** `local`
- **Root:** `storage/app/public`
- **Visibility:** `public`
- **URL:** `/storage`

**Directory Structure:**

```
storage/
├── app/
│   ├── public/
│   │   ├── avatars/          # Avatar uploads
│   │   │   ├── xyz123.jpg
│   │   │   ├── abc456.png
│   │   ├── products/         # Product images
│   │   │   ├── thumbnails/
│   │   │   ├── images/
│   │   ├── verifications/    # Verification documents
```

**Symbolic Link:**

```bash
php artisan storage:link
```

This creates a symbolic link from `public/storage` → `storage/app/public`, making files publicly accessible.

---

## Security Features

### 1. File Validation

- Only image files (JPEG, PNG) accepted
- Maximum file size limit (2MB)
- Dimension validation prevents extremely large images
- File type verified by Laravel's validation

### 2. Authentication

- All avatar endpoints require authentication (`auth:sanctum` middleware)
- Only the authenticated user can update/delete their own avatar

### 3. Secure Storage

- Files stored outside public directory
- Accessed via symbolic link
- Unique filenames prevent collisions
- Old files automatically deleted on update

### 4. Input Sanitization

- Laravel's validation handles input sanitization
- File uploads are validated before processing
- No direct file path manipulation from user input

---

## Usage Examples

### Mobile App Integration (Swift - iOS)

```swift
func uploadAvatar(image: UIImage, token: String) {
    guard let imageData = image.jpegData(compressionQuality: 0.8) else { return }
    
    let url = URL(string: "http://localhost:8000/api/v1/user/avatar")!
    var request = URLRequest(url: url)
    request.httpMethod = "POST"
    request.setValue("Bearer \(token)", forHTTPHeaderField: "Authorization")
    
    let boundary = UUID().uuidString
    request.setValue("multipart/form-data; boundary=\(boundary)", 
                    forHTTPHeaderField: "Content-Type")
    
    var body = Data()
    body.append("--\(boundary)\r\n")
    body.append("Content-Disposition: form-data; name=\"avatar\"; filename=\"avatar.jpg\"\r\n")
    body.append("Content-Type: image/jpeg\r\n\r\n")
    body.append(imageData)
    body.append("\r\n--\(boundary)--\r\n")
    
    request.httpBody = body
    
    URLSession.shared.dataTask(with: request) { data, response, error in
        // Handle response
    }.resume()
}
```

### Mobile App Integration (Kotlin - Android)

```kotlin
fun uploadAvatar(file: File, token: String) {
    val client = OkHttpClient()
    
    val requestBody = MultipartBody.Builder()
        .setType(MultipartBody.FORM)
        .addFormDataPart(
            "avatar",
            file.name,
            file.asRequestBody("image/jpeg".toMediaType())
        )
        .build()
    
    val request = Request.Builder()
        .url("http://localhost:8000/api/v1/user/avatar")
        .addHeader("Authorization", "Bearer $token")
        .post(requestBody)
        .build()
    
    client.newCall(request).enqueue(object : Callback {
        override fun onResponse(call: Call, response: Response) {
            // Handle success
        }
        
        override fun onFailure(call: Call, e: IOException) {
            // Handle error
        }
    })
}
```

### Web Frontend (JavaScript/Fetch)

```javascript
async function uploadAvatar(file, token) {
    const formData = new FormData();
    formData.append('avatar', file);
    
    const response = await fetch('http://localhost:8000/api/v1/user/avatar', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
        },
        body: formData
    });
    
    const data = await response.json();
    
    if (response.ok) {
        console.log('Avatar URL:', data.data.avatar_url);
    } else {
        console.error('Errors:', data.errors);
    }
}
```

---

## Testing

### Manual Testing with cURL

**1. Upload Avatar:**

```bash
# Create test user and login
TOKEN=$(curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}' \
  | jq -r '.token')

# Upload avatar
curl -X POST http://localhost:8000/api/v1/user/avatar \
  -H "Authorization: Bearer $TOKEN" \
  -F "avatar=@test-image.jpg"
```

**2. Delete Avatar:**

```bash
curl -X DELETE http://localhost:8000/api/v1/user/avatar \
  -H "Authorization: Bearer $TOKEN"
```

**3. Verify Avatar in Profile:**

```bash
curl http://localhost:8000/api/v1/user \
  -H "Authorization: Bearer $TOKEN"
```

---

## Error Handling

### Common Errors

| Status | Error | Solution |
|--------|-------|----------|
| 401 | Unauthenticated | Provide valid Bearer token |
| 422 | File too large | Reduce image size to under 2MB |
| 422 | Invalid file type | Use JPEG or PNG format |
| 422 | Dimensions too small | Use image at least 100x100px |
| 422 | Dimensions too large | Use image no larger than 2000x2000px |
| 500 | Storage error | Check storage permissions |

### Troubleshooting

**Issue: "The link [public/storage] has already been declared"**

```bash
# Remove old link and recreate
rm public/storage
php artisan storage:link
```

**Issue: "Permission denied" when uploading**

```bash
# Fix storage permissions
chmod -R 775 storage
chown -R www-data:www-data storage
```

**Issue: Avatar URL returns 404**

```bash
# Ensure symbolic link exists
php artisan storage:link

# Verify file exists
ls -la storage/app/public/avatars/
```

---

## Best Practices

### For Mobile Developers

1. **Image Compression:** Compress images before upload to reduce bandwidth
2. **Progress Indicator:** Show upload progress to users
3. **Error Handling:** Display user-friendly error messages
4. **Retry Logic:** Implement retry for failed uploads
5. **Caching:** Cache avatar URLs to reduce API calls
6. **Lazy Loading:** Load avatars on-demand using image libraries

### For Backend

1. **Storage Cleanup:** Regularly clean up orphaned files
2. **Monitoring:** Monitor storage usage
3. **Backup:** Include avatar directory in backups
4. **CDN:** Consider using CDN for production avatar delivery
5. **Image Optimization:** Implement automatic image optimization

---

## Production Considerations

### 1. CDN Integration

For production, serve avatars from a CDN:

```php
// Update User model accessor
public function getAvatarUrlAttribute(): ?string
{
    if (!$this->avatar_path) {
        return null;
    }
    
    if (config('app.env') === 'production') {
        return config('app.cdn_url') . '/' . $this->avatar_path;
    }
    
    return asset('storage/' . $this->avatar_path);
}
```

### 2. Image Processing

Consider adding automatic image processing:

```bash
composer require intervention/image
```

```php
use Intervention\Image\Laravel\Facades\Image;

public function updateAvatar(User $user, UploadedFile $avatar): User
{
    // Process and optimize image
    $image = Image::read($avatar);
    $image->resize(500, 500, function ($constraint) {
        $constraint->aspectRatio();
        $constraint->upsize();
    });
    
    // Generate filename
    $filename = uniqid() . '.jpg';
    $path = 'avatars/' . $filename;
    
    // Save to storage
    Storage::disk('public')->put($path, $image->encode('jpg', 80));
    
    // Delete old avatar
    if ($user->avatar_path) {
        Storage::disk('public')->delete($user->avatar_path);
    }
    
    // Update user
    $user->update(['avatar_path' => $path]);
    
    return $user->fresh();
}
```

### 3. Rate Limiting

Add rate limiting for avatar uploads:

```php
// In routes/api.php
Route::middleware(['auth:sanctum', 'throttle:5,1'])->group(function () {
    Route::post('/user/avatar', [AuthController::class, 'updateAvatar']);
});
```

Limits to 5 uploads per minute per user.

---

## Summary

This avatar upload feature provides a complete, production-ready solution for user profile images with:

✅ **Clean Architecture:** Separation of concerns (Controller → Service → Model)
✅ **Comprehensive Validation:** File type, size, and dimension checks
✅ **Secure Storage:** Files stored securely with automatic cleanup
✅ **RESTful API:** Standard endpoints following REST conventions
✅ **Mobile-Friendly:** Easy integration with iOS, Android, and web apps
✅ **Error Handling:** Clear error messages and status codes
✅ **Best Practices:** Follows Laravel and PSR-12 standards
✅ **Documentation:** Complete API reference and usage examples

---

**Last Updated:** December 4, 2025
