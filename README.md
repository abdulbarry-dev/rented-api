<!-- filepath: /home/vortex/Desktop/work-space/projects/rented-api/README.md -->
# Rented Marketplace API - Complete Reference

## Table of Contents

1. [Overview](#overview)
2. [Base URL & Versioning](#base-url--versioning)
3. [Authentication](#authentication)
4. [Common Headers](#common-headers)
5. [Response Format](#response-format)
6. [Error Handling](#error-handling)
7. [Rate Limiting](#rate-limiting)
8. [API Endpoints](#api-endpoints)
   - [Health Check](#health-check)
   - [Authentication](#authentication-endpoints)
   - [User Profile](#user-profile-endpoints)
   - [Avatar Management](#avatar-management-endpoints)
   - [Categories](#categories-endpoints)
   - [Products](#products-endpoints)
   - [User Verification](#user-verification-endpoints)
   - [Product Management](#product-management-endpoints)
   - [Rentals](#rentals-endpoints)
   - [Purchases](#purchases-endpoints)
9. [Status Codes](#status-codes)
10. [Pagination](#pagination)
11. [File Uploads](#file-uploads)
12. [Best Practices](#best-practices)

---

## Overview

The Rented Marketplace API is a RESTful API that allows users to rent or purchase items. The API provides endpoints for user management, product listings, rentals, and purchases.

### Key Features

- Token-based authentication (Laravel Sanctum)
- User verification system with ID document upload
- Avatar upload and management
- Product management with image uploads (CRUD operations)
- Rental and purchase flows
- File upload support (images and documents)
- Pagination support
- Caching for improved performance (Laravel Octane with Swoole)

### API Characteristics

- **Architecture**: REST
- **Data Format**: JSON
- **Authentication**: Bearer Token (Sanctum)
- **Version**: v1
- **Protocol**: HTTPS (production), HTTP (development)
- **Performance**: Laravel Octane with Swoole for high-performance request handling

---

## Base URL & Versioning

### Development

```
http://localhost:8000/api/v1
```

### Production

```
https://api.rentedmarketplace.com/api/v1
```

### Docker (Local)

```
http://localhost:8000/api/v1
```

### API Versioning

All endpoints are prefixed with `/api/v1/`. Future versions will use `/api/v2/`, etc.

---

## Authentication

The API uses Laravel Sanctum for token-based authentication. Most endpoints require authentication except for public endpoints like viewing products and categories.

### Authentication Flow

1. **Register** or **Login** to receive an authentication token
2. Include the token in the `Authorization` header for all authenticated requests
3. **Logout** to revoke the token when done

### Token Format

```
Authorization: Bearer {your-token-here}
```

### Token Lifecycle

- Tokens do not expire automatically
- Tokens are revoked on logout
- Users can have multiple active tokens (different devices)
- Tokens are deleted when user changes password

---

## Common Headers

### Required Headers (All Requests)

```http
Accept: application/json
Content-Type: application/json
```

### Authenticated Requests

```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer {token}
```

### File Upload Requests

```http
Accept: application/json
Content-Type: multipart/form-data
Authorization: Bearer {token}
```

---

## Response Format

All API responses follow a consistent JSON structure.

### Success Response (Single Resource)

```json
{
  "message": "Operation successful",
  "data": {
    "id": 1,
    "name": "Resource name",
    "created_at": "2025-12-03T14:30:00.000000Z"
  }
}
```

### Success Response (Collection)

```json
{
  "data": [
    {
      "id": 1,
      "name": "Item 1"
    },
    {
      "id": 2,
      "name": "Item 2"
    }
  ]
}
```

### Success Response (Paginated)

```json
{
  "data": [...],
  "links": {
    "first": "http://api.example.com/products?page=1",
    "last": "http://api.example.com/products?page=10",
    "prev": null,
    "next": "http://api.example.com/products?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 10,
    "per_page": 15,
    "to": 15,
    "total": 150
  }
}
```

### Success Response (No Content)

```
HTTP/1.1 204 No Content
```

---

## Error Handling

### Error Response Structure

```json
{
  "message": "Error description",
  "errors": {
    "field_name": [
      "Validation error message"
    ]
  }
}
```

### Validation Error Example

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The email field is required."
    ],
    "password": [
      "The password must be at least 8 characters."
    ]
  }
}
```

### Authentication Error

```json
{
  "message": "Unauthenticated."
}
```

### Authorization Error

```json
{
  "message": "This action is unauthorized."
}
```

### Not Found Error

```json
{
  "message": "Resource not found."
}
```

### Server Error

```json
{
  "message": "Server Error",
  "error": "Detailed error message (only in development)"
}
```

---

## Rate Limiting

The API implements rate limiting to prevent abuse.

### Default Limits

- **Public Endpoints**: 60 requests per minute
- **Authenticated Endpoints**: 120 requests per minute

### Rate Limit Headers

```http
X-RateLimit-Limit: 120
X-RateLimit-Remaining: 119
X-RateLimit-Reset: 1638360000
```

### Rate Limit Exceeded Response

```json
{
  "message": "Too Many Requests"
}
```

**Status Code**: 429 Too Many Requests

---

## API Endpoints

### Health Check

#### Check API Status

Get the current status of the API.

**Endpoint**: `GET /`

**Authentication**: Not required

**Request Example**:

```bash
curl -X GET "http://localhost:8000/api/v1/" \
  -H "Accept: application/json"
```

**Response** (200 OK):

```json
{
  "status": "success",
  "message": "API is working",
  "version": "v1",
  "timestamp": "2025-12-03T14:30:45.000000Z"
}
```

---

### Authentication Endpoints

#### Register User

Create a new user account.

**Endpoint**: `POST /register`

**Authentication**: Not required

**Request Body**:

```json
{
  "name": "John Doe",
  "email": "john.doe@example.com",
  "password": "SecurePass123!",
  "password_confirmation": "SecurePass123!"
}
```

**Validation Rules**:

- `name`: required, string, max:255
- `email`: required, email, unique, max:255
- `password`: required, string, min:8, confirmed

**Response** (201 Created):

```json
{
  "message": "User registered successfully",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "avatar_url": null,
    "verification_status": "pending",
    "verified_at": null,
    "created_at": "2025-12-03T14:30:45.000000Z",
    "updated_at": "2025-12-03T14:30:45.000000Z"
  },
  "token": "1|AbCdEfGhIjKlMnOpQrStUvWxYz1234567890"
}
```

**Error Responses**:

*422 Unprocessable Entity* - Validation failed

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The email has already been taken."
    ],
    "password": [
      "The password confirmation does not match."
    ]
  }
}
```

---

#### Login User

Authenticate a user and receive an access token.

**Endpoint**: `POST /login`

**Authentication**: Not required

**Request Body**:

```json
{
  "email": "john.doe@example.com",
  "password": "SecurePass123!"
}
```

**Validation Rules**:

- `email`: required, email
- `password`: required, string

**Response** (200 OK):

```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "avatar_url": "http://localhost:8000/storage/avatars/abc123.jpg",
    "verification_status": "verified",
    "verified_at": "2025-12-03T14:30:45.000000Z"
  },
  "token": "2|XyZaBcDeFgHiJkLmNoPqRsTuVwXy0987654321"
}
```

**Error Responses**:

*422 Unprocessable Entity* - Invalid credentials

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The provided credentials are incorrect."
    ]
  }
}
```

---

#### Logout User

Revoke the current access token.

**Endpoint**: `POST /logout`

**Authentication**: Required

**Headers**:

```http
Authorization: Bearer {token}
```

**Response** (200 OK):

```json
{
  "message": "Logged out successfully"
}
```

**Error Responses**:

*401 Unauthorized* - Invalid or missing token

```json
{
  "message": "Unauthenticated."
}
```

---

#### Get Current User

Retrieve the authenticated user's profile.

**Endpoint**: `GET /user`

**Authentication**: Required

**Headers**:

```http
Authorization: Bearer {token}
```

**Response** (200 OK):

```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "avatar_url": "http://localhost:8000/storage/avatars/abc123.jpg",
    "verification_status": "verified",
    "verified_at": "2025-12-03T14:30:45.000000Z",
    "created_at": "2025-12-03T14:30:45.000000Z",
    "updated_at": "2025-12-03T14:30:45.000000Z"
  }
}
```

---

### User Profile Endpoints

#### Update User Profile

Update the authenticated user's profile information.

**Endpoint**: `PUT /user/profile`

**Authentication**: Required

**Headers**:

```http
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body** (Update Name):

```json
{
  "name": "John Updated Doe"
}
```

**Request Body** (Update Email):

```json
{
  "email": "newemail@example.com"
}
```

**Request Body** (Change Password):

```json
{
  "current_password": "SecurePass123!",
  "password": "NewSecurePass456!",
  "password_confirmation": "NewSecurePass456!"
}
```

**Request Body** (Update Multiple Fields):

```json
{
  "name": "John Updated Doe",
  "email": "newemail@example.com",
  "current_password": "SecurePass123!",
  "password": "NewSecurePass456!",
  "password_confirmation": "NewSecurePass456!"
}
```

**Validation Rules**:

- `name`: sometimes, string, max:255
- `email`: sometimes, email, unique (excluding current user), max:255
- `current_password`: required_with:password, string
- `password`: sometimes, string, min:8, confirmed

**Response** (200 OK):

```json
{
  "message": "Profile updated successfully",
  "data": {
    "id": 1,
    "name": "John Updated Doe",
    "email": "newemail@example.com",
    "avatar_url": "http://localhost:8000/storage/avatars/abc123.jpg",
    "verification_status": "verified",
    "verified_at": "2025-12-03T14:30:45.000000Z",
    "updated_at": "2025-12-03T15:20:30.000000Z"
  }
}
```

**Error Responses**:

*400 Bad Request* - Current password incorrect

```json
{
  "message": "Current password is incorrect."
}
```

*422 Unprocessable Entity* - Validation failed

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The email has already been taken."
    ],
    "password": [
      "The password must be at least 8 characters."
    ]
  }
}
```

---

### Avatar Management Endpoints

#### Upload/Update Avatar

Upload or update the authenticated user's profile avatar.

**Endpoint**: `POST /user/avatar`

**Authentication**: Required

**Headers**:

```http
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body** (multipart/form-data):

- `avatar` (file, required): Profile avatar image

**Validation Rules**:

- `avatar`: required, image, mimes:jpeg,jpg,png, max:2048 (2MB), dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000

**cURL Example**:

```bash
curl -X POST "http://localhost:8000/api/v1/user/avatar" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -F "avatar=@/path/to/avatar.jpg"
```

**Response** (200 OK):

```json
{
  "message": "Avatar updated successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "avatar_url": "http://localhost:8000/storage/avatars/1_abc123def456.jpg",
    "verification_status": "verified",
    "verified_at": "2025-12-03T14:30:45.000000Z",
    "updated_at": "2025-12-04T10:15:30.000000Z"
  }
}
```

**Note**: When updating an avatar, the old avatar file is automatically deleted.

**Error Responses**:

*422 Unprocessable Entity* - Validation failed

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "avatar": [
      "The avatar must be an image.",
      "The avatar must not be greater than 2048 kilobytes.",
      "The avatar must have minimum dimensions of 100x100 pixels.",
      "The avatar must have maximum dimensions of 2000x2000 pixels."
    ]
  }
}
```

---

#### Delete Avatar

Remove the authenticated user's profile avatar.

**Endpoint**: `DELETE /user/avatar`

**Authentication**: Required

**Headers**:

```http
Authorization: Bearer {token}
```

**Response** (200 OK):

```json
{
  "message": "Avatar deleted successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "avatar_url": null,
    "verification_status": "verified",
    "verified_at": "2025-12-03T14:30:45.000000Z",
    "updated_at": "2025-12-04T10:20:00.000000Z"
  }
}
```

**Error Responses**:

*404 Not Found* - No avatar to delete

```json
{
  "message": "No avatar found to delete."
}
```

---

### Categories Endpoints

#### Get All Categories

Retrieve a list of all active categories.

**Endpoint**: `GET /categories`

**Authentication**: Not required

**Response** (200 OK):

```json
{
  "data": [
    {
      "id": 1,
      "name": "Electronics",
      "slug": "electronics",
      "description": "Electronic devices and gadgets",
      "is_active": true
    },
    {
      "id": 2,
      "name": "Photography",
      "slug": "photography",
      "description": "Cameras, lenses, and photography equipment",
      "is_active": true
    },
    {
      "id": 3,
      "name": "Sports Equipment",
      "slug": "sports-equipment",
      "description": "Sports gear and athletic equipment",
      "is_active": true
    }
  ]
}
```

**Cache**: This endpoint is cached for 1 hour.

---

#### Get Single Category

Retrieve details of a specific category.

**Endpoint**: `GET /categories/{id}`

**Authentication**: Not required

**Path Parameters**:

- `id` (integer, required): Category ID

**Response** (200 OK):

```json
{
  "data": {
    "id": 1,
    "name": "Electronics",
    "slug": "electronics",
    "description": "Electronic devices and gadgets",
    "is_active": true
  }
}
```

**Error Responses**:

*404 Not Found* - Category doesn't exist

```json
{
  "message": "Resource not found."
}
```

---

### Products Endpoints

#### Get All Products

Retrieve a paginated list of all available products.

**Endpoint**: `GET /products`

**Authentication**: Not required

**Query Parameters**:

- `page` (integer, optional): Page number (default: 1)
- `per_page` (integer, optional): Items per page (default: 15, max: 100)

**Request Example**:

```bash
GET /products?page=2&per_page=20
```

**Response** (200 OK):

```json
{
  "data": [
    {
      "id": 1,
      "title": "Canon EOS R5 Camera",
      "description": "Professional mirrorless camera",
      "price_per_day": "50.00",
      "is_for_sale": true,
      "sale_price": "2500.00",
      "is_available": true,
      "thumbnail_url": "http://localhost:8000/storage/products/thumbnails/camera1.jpg",
      "image_urls": [
        "http://localhost:8000/storage/products/images/camera1-1.jpg",
        "http://localhost:8000/storage/products/images/camera1-2.jpg"
      ],
      "category": {
        "id": 2,
        "name": "Photography",
        "slug": "photography"
      }
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/v1/products?page=1",
    "last": "http://localhost:8000/api/v1/products?page=10",
    "prev": "http://localhost:8000/api/v1/products?page=1",
    "next": "http://localhost:8000/api/v1/products?page=3"
  },
  "meta": {
    "current_page": 2,
    "from": 16,
    "last_page": 10,
    "per_page": 15,
    "to": 30,
    "total": 150
  }
}
```

**Cache**: This endpoint is cached for 10 minutes.

---

#### Get Single Product

Retrieve details of a specific product.

**Endpoint**: `GET /products/{id}`

**Authentication**: Not required

**Path Parameters**:

- `id` (integer, required): Product ID

**Response** (200 OK):

```json
{
  "data": {
    "id": 1,
    "title": "Canon EOS R5 Camera",
    "description": "Professional mirrorless camera with 45MP full-frame sensor",
    "price_per_day": "50.00",
    "is_for_sale": true,
    "sale_price": "2500.00",
    "is_available": true,
    "thumbnail_url": "http://localhost:8000/storage/products/thumbnails/camera1.jpg",
    "image_urls": [
      "http://localhost:8000/storage/products/images/camera1-1.jpg",
      "http://localhost:8000/storage/products/images/camera1-2.jpg",
      "http://localhost:8000/storage/products/images/camera1-3.jpg"
    ],
    "category": {
      "id": 2,
      "name": "Photography",
      "slug": "photography",
      "description": "Cameras, lenses, and photography equipment"
    },
    "owner": {
      "id": 5,
      "name": "Jane Smith",
      "email": "jane.smith@example.com",
      "avatar_url": "http://localhost:8000/storage/avatars/5_xyz789.jpg"
    },
    "created_at": "2025-12-01T10:30:00.000000Z",
    "updated_at": "2025-12-02T14:20:00.000000Z"
  }
}
```

**Error Responses**:

*404 Not Found* - Product doesn't exist

```json
{
  "message": "Resource not found."
}
```

**Cache**: This endpoint is cached for 10 minutes.

---

### User Verification Endpoints

#### Upload Verification Documents

Upload identification documents for user verification. This endpoint accepts up to 3 images: front of ID, back of ID, and a selfie with ID.

**Endpoint**: `POST /verify`

**Authentication**: Required

**Headers**:

```http
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body** (multipart/form-data):

- `id_front` (file, required): Front of ID document (JPEG, JPG, PNG)
- `id_back` (file, required): Back of ID document (JPEG, JPG, PNG)
- `selfie` (file, required): Selfie holding ID document (JPEG, JPG, PNG)
- `document_type` (string, optional): Type of document (passport, national_id, driver_license)

**Validation Rules**:

- `id_front`: required, image, mimes:jpeg,jpg,png, max:5120 (5MB)
- `id_back`: required, image, mimes:jpeg,jpg,png, max:5120 (5MB)
- `selfie`: required, image, mimes:jpeg,jpg,png, max:5120 (5MB)
- `document_type`: nullable, in:passport,national_id,driver_license

**cURL Example**:

```bash
curl -X POST "http://localhost:8000/api/v1/verify" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -F "id_front=@/path/to/id_front.jpg" \
  -F "id_back=@/path/to/id_back.jpg" \
  -F "selfie=@/path/to/selfie.jpg" \
  -F "document_type=national_id"
```

**Response** (201 Created):

```json
{
  "message": "Verification documents uploaded successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "verification_status": "pending",
    "document_type": "national_id",
    "has_id_front": true,
    "has_id_back": true,
    "has_selfie": true,
    "submitted_at": "2025-12-03T14:30:45.000000Z",
    "updated_at": "2025-12-03T14:30:45.000000Z"
  }
}
```

**Error Responses**:

*422 Unprocessable Entity* - Validation failed

```json
{
  "message": "Verification upload failed",
  "errors": {
    "id_front": [
      "The id front must be an image.",
      "The id front must be a file of type: jpeg, jpg, png."
    ],
    "selfie": [
      "The selfie field is required.",
      "The selfie must not be greater than 5120 kilobytes."
    ]
  }
}
```

*422 Unprocessable Entity* - Already verified or pending

```json
{
  "message": "You already have a pending verification request or you are already verified."
}
```

---

#### Get Verification Status

Check the status of the user's verification request.

**Endpoint**: `GET /verify/status`

**Authentication**: Required

**Headers**:

```http
Authorization: Bearer {token}
```

**Response** (200 OK) - Verified:

```json
{
  "data": {
    "verification_status": "verified",
    "document_type": "national_id",
    "has_id_front": true,
    "has_id_back": true,
    "has_selfie": true,
    "submitted_at": "2025-12-03T14:30:45.000000Z",
    "verified_at": "2025-12-03T16:45:20.000000Z"
  }
}
```

**Response** (200 OK) - Pending:

```json
{
  "data": {
    "verification_status": "pending",
    "document_type": "national_id",
    "has_id_front": true,
    "has_id_back": true,
    "has_selfie": true,
    "submitted_at": "2025-12-03T14:30:45.000000Z",
    "verified_at": null
  }
}
```

**Response** (200 OK) - Rejected:

```json
{
  "data": {
    "verification_status": "rejected",
    "document_type": "passport",
    "has_id_front": true,
    "has_id_back": true,
    "has_selfie": true,
    "submitted_at": "2025-12-03T14:30:45.000000Z",
    "verified_at": null,
    "admin_notes": "Documents are not clear. Please upload higher quality images."
  }
}
```

**Response** (200 OK) - Unverified (No submission):

```json
{
  "data": {
    "verification_status": "unverified",
    "document_type": null,
    "has_id_front": false,
    "has_id_back": false,
    "has_selfie": false,
    "submitted_at": null,
    "verified_at": null
  }
}
```

**Possible Status Values**:

- `unverified`: User has not submitted verification documents
- `pending`: Documents are under review
- `verified`: User is verified
- `rejected`: Verification failed, resubmission required

---

#### View Verification Image (Secure)

Securely view a verification image. Only the document owner can access their images. This endpoint is rate-limited to 60 requests per minute.

**Endpoint**: `GET /verify/image/{imageType}`

**Authentication**: Required (Owner only)

**Headers**:

```http
Authorization: Bearer {token}
```

**Path Parameters**:

- `imageType` (string, required): Type of image to view. Must be one of: `id_front`, `id_back`, `selfie`

**Rate Limiting**: 60 requests per minute

**Request Example**:

```bash
curl -X GET "http://localhost:8000/api/v1/verify/image/id_front" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

**Response** (200 OK):

Returns the image file directly with appropriate headers:

```
Content-Type: image/jpeg
Content-Disposition: inline
Cache-Control: private, no-cache, no-store, must-revalidate
Pragma: no-cache
Expires: 0
X-Content-Type-Options: nosniff
```

**Error Responses**:

*400 Bad Request* - Invalid image type

```json
{
  "message": "Invalid image type. Must be: id_front, id_back, or selfie."
}
```

*403 Forbidden* - Not authorized (trying to access another user's images)

```json
{
  "message": "This action is unauthorized."
}
```

*404 Not Found* - No verification documents

```json
{
  "message": "No verification documents found."
}
```

*404 Not Found* - Specific image not found

```json
{
  "message": "Image not found."
}
```

*429 Too Many Requests* - Rate limit exceeded

```json
{
  "message": "Too Many Attempts."
}
```

**Flutter Implementation Example**:

```dart
Future<Uint8List?> loadVerificationImage(String imageType) async {
  final response = await http.get(
    Uri.parse('$baseUrl/verify/image/$imageType'),
    headers: {
      'Authorization': 'Bearer $token',
      'Accept': 'application/json',
    },
  );

  if (response.statusCode == 200) {
    return response.bodyBytes;
  } else if (response.statusCode == 404) {
    // Image not found
    return null;
  } else if (response.statusCode == 429) {
    // Rate limit exceeded
    throw Exception('Too many requests. Please try again later.');
  }
  
  throw Exception('Failed to load image');
}

// Usage in Widget
Image.memory(
  imageBytes,
  fit: BoxFit.cover,
  errorBuilder: (context, error, stackTrace) {
    return Icon(Icons.error);
  },
)
```

---

### Product Management Endpoints

#### Create Product

Create a new product listing with images. **Requires verified user**.

**Endpoint**: `POST /products`

**Authentication**: Required (Verified users only)

**Headers**:

```http
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body** (multipart/form-data):

- `category_id` (integer, required): Category ID
- `title` (string, required): Product title
- `description` (string, required): Product description
- `price_per_day` (numeric, required): Daily rental price
- `is_for_sale` (boolean, optional): Is product for sale? (default: false)
- `sale_price` (numeric, required_if:is_for_sale): Sale price (required if is_for_sale is true)
- `images` (array, optional): Product images (1-5 images, each max 2MB)

**Validation Rules**:

- `category_id`: required, exists:categories,id
- `title`: required, string, max:255
- `description`: required, string
- `price_per_day`: required, numeric, min:1
- `is_for_sale`: boolean
- `sale_price`: required_if:is_for_sale,true, numeric, min:1
- `images`: nullable, array, min:1, max:5
- `images.*`: image, mimes:jpeg,jpg,png, max:2048

**cURL Example**:

```bash
curl -X POST "http://localhost:8000/api/v1/products" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -F "category_id=2" \
  -F "title=Canon EOS R5 Camera" \
  -F "description=Professional mirrorless camera with 45MP sensor" \
  -F "price_per_day=50" \
  -F "is_for_sale=true" \
  -F "sale_price=2500" \
  -F "images[]=@/path/to/image1.jpg" \
  -F "images[]=@/path/to/image2.jpg" \
  -F "images[]=@/path/to/image3.jpg"
```

**Response** (201 Created):

```json
{
  "message": "Product created successfully",
  "data": {
    "id": 25,
    "title": "Canon EOS R5 Camera",
    "description": "Professional mirrorless camera with 45MP sensor",
    "price_per_day": "50.00",
    "is_for_sale": true,
    "sale_price": "2500.00",
    "is_available": true,
    "thumbnail_url": null,
    "image_urls": [
      "http://localhost:8000/storage/products/images/abc123xyz.jpg",
      "http://localhost:8000/storage/products/images/def456uvw.jpg",
      "http://localhost:8000/storage/products/images/ghi789rst.jpg"
    ],
    "category": {
      "id": 2,
      "name": "Photography",
      "slug": "photography"
    },
    "owner": {
      "id": 1,
      "name": "John Doe",
      "email": "john.doe@example.com",
      "avatar_url": "http://localhost:8000/storage/avatars/user1.jpg"
    },
    "created_at": "2025-12-03T14:30:45.000000Z",
    "updated_at": "2025-12-03T14:30:45.000000Z"
  }
}
```

**Error Responses**:

*403 Forbidden* - User not verified

```json
{
  "message": "Your account must be verified to perform this action."
}
```

*422 Unprocessable Entity* - Validation failed

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "title": [
      "The title field is required."
    ],
    "sale_price": [
      "The sale price field is required when is for sale is true."
    ],
    "images": [
      "You can upload a maximum of 5 images."
    ],
    "images.0": [
      "The images.0 must be an image.",
      "The images.0 must not be greater than 2048 kilobytes."
    ]
  }
}
```

**Flutter Implementation Example**:

```dart
Future<Map<String, dynamic>> createProduct({
  required int categoryId,
  required String title,
  required String description,
  required double pricePerDay,
  bool isForSale = false,
  double? salePrice,
  List<File>? images,
}) async {
  var request = http.MultipartRequest(
    'POST',
    Uri.parse('$baseUrl/products'),
  );

  request.headers.addAll({
    'Authorization': 'Bearer $token',
    'Accept': 'application/json',
  });

  request.fields['category_id'] = categoryId.toString();
  request.fields['title'] = title;
  request.fields['description'] = description;
  request.fields['price_per_day'] = pricePerDay.toString();
  request.fields['is_for_sale'] = isForSale ? '1' : '0';
  
  if (isForSale && salePrice != null) {
    request.fields['sale_price'] = salePrice.toString();
  }

  // Add images (max 5)
  if (images != null && images.isNotEmpty) {
    for (var i = 0; i < images.length && i < 5; i++) {
      request.files.add(await http.MultipartFile.fromPath(
        'images[]',
        images[i].path,
        contentType: MediaType('image', 'jpeg'),
      ));
    }
  }

  final response = await request.send();
  final responseBody = await response.stream.bytesToString();
  
  if (response.statusCode == 201) {
    return json.decode(responseBody);
  } else if (response.statusCode == 403) {
    throw Exception('Your account must be verified to create products');
  } else if (response.statusCode == 422) {
    final errors = json.decode(responseBody);
    throw Exception(errors['message']);
  }
  
  throw Exception('Failed to create product');
}
```

---

#### Update Product

Update an existing product. **Only product owner can update**. When updating with new images, old images are automatically deleted.

**Endpoint**: `PUT /products/{id}` or `PATCH /products/{id}`

**Authentication**: Required (Product owner only)

**Headers**:

```http
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Path Parameters**:

- `id` (integer, required): Product ID

**Request Body** (multipart/form-data):

All fields are optional. Include only the fields you want to update.

- `category_id` (integer, optional): Category ID
- `title` (string, optional): Product title
- `description` (string, optional): Product description
- `price_per_day` (numeric, optional): Daily rental price
- `is_for_sale` (boolean, optional): Is product for sale?
- `sale_price` (numeric, optional): Sale price
- `is_available` (boolean, optional): Product availability status
- `images` (array, optional): New product images (1-5 images, replaces all old images)

**Validation Rules**:

- `category_id`: sometimes, exists:categories,id
- `title`: sometimes, string, max:255
- `description`: sometimes, string
- `price_per_day`: sometimes, numeric, min:1
- `is_for_sale`: boolean
- `sale_price`: required_if:is_for_sale,true, numeric, min:1
- `is_available`: boolean
- `images`: nullable, array, min:1, max:5
- `images.*`: image, mimes:jpeg,jpg,png, max:2048

**cURL Example** (Update text fields only):

```bash
curl -X PUT "http://localhost:8000/api/v1/products/25" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "Updated Camera Title",
    "price_per_day": 55.00,
    "is_available": true
  }'
```

**cURL Example** (Update with new images):

```bash
curl -X POST "http://localhost:8000/api/v1/products/25" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -F "_method=PUT" \
  -F "title=Updated Camera Title" \
  -F "price_per_day=55" \
  -F "images[]=@/path/to/new_image1.jpg" \
  -F "images[]=@/path/to/new_image2.jpg"
```

**Response** (200 OK):

```json
{
  "message": "Product updated successfully",
  "data": {
    "id": 25,
    "title": "Updated Camera Title",
    "description": "Professional mirrorless camera with 45MP sensor",
    "price_per_day": "55.00",
    "is_for_sale": true,
    "sale_price": "2500.00",
    "is_available": true,
    "thumbnail_url": null,
    "image_urls": [
      "http://localhost:8000/storage/products/images/new_abc123.jpg",
      "http://localhost:8000/storage/products/images/new_def456.jpg"
    ],
    "category": {
      "id": 2,
      "name": "Photography",
      "slug": "photography"
    },
    "owner": {
      "id": 1,
      "name": "John Doe",
      "email": "john.doe@example.com",
      "avatar_url": "http://localhost:8000/storage/avatars/user1.jpg"
    },
    "created_at": "2025-12-03T14:30:45.000000Z",
    "updated_at": "2025-12-03T15:30:45.000000Z"
  }
}
```

**Error Responses**:

*403 Forbidden* - Not product owner

```json
{
  "message": "This action is unauthorized."
}
```

*404 Not Found* - Product doesn't exist

```json
{
  "message": "Product not found"
}
```

*422 Unprocessable Entity* - Validation failed

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "price_per_day": [
      "The price per day must be at least 1."
    ],
    "images": [
      "You can upload a maximum of 5 images."
    ]
  }
}
```

**Flutter Implementation Example**:

```dart
Future<Map<String, dynamic>> updateProduct({
  required int productId,
  String? title,
  String? description,
  double? pricePerDay,
  bool? isAvailable,
  List<File>? newImages,
}) async {
  // For text-only updates
  if (newImages == null || newImages.isEmpty) {
    final response = await http.put(
      Uri.parse('$baseUrl/products/$productId'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: json.encode({
        if (title != null) 'title': title,
        if (description != null) 'description': description,
        if (pricePerDay != null) 'price_per_day': pricePerDay,
        if (isAvailable != null) 'is_available': isAvailable,
      }),
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    }
    throw Exception('Failed to update product');
  }

  // For updates with images
  var request = http.MultipartRequest(
    'POST',
    Uri.parse('$baseUrl/products/$productId'),
  );

  request.headers.addAll({
    'Authorization': 'Bearer $token',
    'Accept': 'application/json',
  });

  request.fields['_method'] = 'PUT';
  if (title != null) request.fields['title'] = title;
  if (description != null) request.fields['description'] = description;
  if (pricePerDay != null) request.fields['price_per_day'] = pricePerDay.toString();
  if (isAvailable != null) request.fields['is_available'] = isAvailable ? '1' : '0';

  // Add new images (replaces all old images)
  for (var i = 0; i < newImages.length && i < 5; i++) {
    request.files.add(await http.MultipartFile.fromPath(
      'images[]',
      newImages[i].path,
      contentType: MediaType('image', 'jpeg'),
    ));
  }

  final response = await request.send();
  final responseBody = await response.stream.bytesToString();
  
  if (response.statusCode == 200) {
    return json.decode(responseBody);
  } else if (response.statusCode == 403) {
    throw Exception('Unauthorized: You can only update your own products');
  }
  
  throw Exception('Failed to update product');
}
```

---

#### Delete Product

Delete a product listing. **Only product owner can delete**. All product images are automatically deleted from storage.

**Endpoint**: `DELETE /products/{id}`

**Authentication**: Required (Product owner only)

**Headers**:

```http
Authorization: Bearer {token}
```

**Path Parameters**:

- `id` (integer, required): Product ID

**Response** (200 OK):

```json
{
  "message": "Product deleted successfully"
}
```

**Note**: All product images are automatically deleted from storage.

**Error Responses**:

*403 Forbidden* - Not product owner

```json
{
  "message": "This action is unauthorized."
}
```

*404 Not Found* - Product doesn't exist

```json
{
  "message": "Product not found"
}
```

**Flutter Implementation Example**:

```dart
Future<void> deleteProduct(int productId) async {
  final response = await http.delete(
    Uri.parse('$baseUrl/products/$productId'),
    headers: {
      'Authorization': 'Bearer $token',
      'Accept': 'application/json',
    },
  );

  if (response.statusCode == 200) {
    final data = json.decode(response.body);
    print(data['message']); // "Product deleted successfully"
    return;
  } else if (response.statusCode == 403) {
    throw Exception('Unauthorized: You can only delete your own products');
  } else if (response.statusCode == 404) {
    throw Exception('Product not found');
  }
  
  throw Exception('Failed to delete product');
}
```

---

#### Get User's Products

Retrieve all products belonging to the authenticated user.

**Endpoint**: `GET /user/products`

**Authentication**: Required

**Headers**:

```http
Authorization: Bearer {token}
```

**Response** (200 OK):

```json
{
  "data": [
    {
      "id": 25,
      "title": "Canon EOS R5 Camera",
      "description": "Professional mirrorless camera",
      "price_per_day": "50.00",
      "is_for_sale": true,
      "sale_price": "2500.00",
      "is_available": true,
      "thumbnail_url": "http://localhost:8000/storage/products/thumbnails/abc123.jpg",
      "image_urls": [
        "http://localhost:8000/storage/products/images/xyz789.jpg"
      ],
      "category": {
        "id": 2,
        "name": "Photography"
      },
      "created_at": "2025-12-03T14:30:45.000000Z"
    }
  ]
}
```

---

### Rentals Endpoints

#### Create Rental Request

Request to rent a product for specific dates.

**Endpoint**: `POST /rentals`

**Authentication**: Required

**Headers**:

```http
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body**:

```json
{
  "product_id": 25,
  "start_date": "2025-12-10",
  "end_date": "2025-12-15",
  "notes": "Need for weekend photoshoot event"
}
```

**Validation Rules**:

- `product_id`: required, exists:products,id
- `start_date`: required, date, after_or_equal:today
- `end_date`: required, date, after:start_date
- `notes`: nullable, string, max:500

**Response** (201 Created):

```json
{
  "message": "Rental request created successfully",
  "data": {
    "id": 10,
    "product": {
      "id": 25,
      "title": "Canon EOS R5 Camera",
      "thumbnail_url": "http://localhost:8000/storage/products/thumbnails/abc123.jpg",
      "price_per_day": "50.00",
      "owner": {
        "id": 5,
        "name": "Jane Smith",
        "avatar_url": "http://localhost:8000/storage/avatars/5_xyz789.jpg"
      }
    },
    "renter": {
      "id": 1,
      "name": "John Doe",
      "email": "john.doe@example.com",
      "avatar_url": "http://localhost:8000/storage/avatars/1_abc123.jpg"
    },
    "start_date": "2025-12-10",
    "end_date": "2025-12-15",
    "total_price": "300.00",
    "status": "pending",
    "notes": "Need for weekend photoshoot event",
    "created_at": "2025-12-03T14:30:45.000000Z"
  }
}
```

**Error Responses**:

*400 Bad Request* - Product not available

```json
{
  "message": "Product is not available for rent."
}
```

*400 Bad Request* - Date conflict

```json
{
  "message": "Product is not available for the selected dates."
}
```

---

#### Update Rental Status

Update the status of a rental request. **Only product owner can update**.

**Endpoint**: `PUT /rentals/{id}`

**Authentication**: Required (Product owner only)

**Headers**:

```http
Authorization: Bearer {token}
Content-Type: application/json
```

**Path Parameters**:

- `id` (integer, required): Rental ID

**Request Body**:

```json
{
  "status": "approved",
  "notes": "Rental approved. Please contact for pickup details."
}
```

**Validation Rules**:

- `status`: required, in:approved,active,completed,cancelled
- `notes`: nullable, string, max:500

**Possible Status Values**:

- `pending`: Initial status
- `approved`: Owner approved the rental
- `active`: Rental is currently active
- `completed`: Rental completed successfully
- `cancelled`: Rental was cancelled

**Response** (200 OK):

```json
{
  "message": "Rental status updated successfully",
  "data": {
    "id": 10,
    "product": {
      "id": 25,
      "title": "Canon EOS R5 Camera",
      "owner": {
        "id": 5,
        "name": "Jane Smith"
      }
    },
    "renter": {
      "id": 1,
      "name": "John Doe"
    },
    "start_date": "2025-12-10",
    "end_date": "2025-12-15",
    "total_price": "300.00",
    "status": "approved",
    "notes": "Rental approved. Please contact for pickup details.",
    "updated_at": "2025-12-03T15:30:45.000000Z"
  }
}
```

**Error Responses**:

*403 Forbidden* - Not product owner

```json
{
  "message": "This action is unauthorized."
}
```

---

#### Get User's Rentals

Retrieve all rental requests made by the authenticated user.

**Endpoint**: `GET /user/rentals`

**Authentication**: Required

**Headers**:

```http
Authorization: Bearer {token}
```

**Response** (200 OK):

```json
{
  "data": [
    {
      "id": 10,
      "product": {
        "id": 25,
        "title": "Canon EOS R5 Camera",
        "thumbnail_url": "http://localhost:8000/storage/products/thumbnails/abc123.jpg",
        "price_per_day": "50.00",
        "owner": {
          "id": 5,
          "name": "Jane Smith"
        }
      },
      "start_date": "2025-12-10",
      "end_date": "2025-12-15",
      "total_price": "300.00",
      "status": "approved",
      "created_at": "2025-12-03T14:30:45.000000Z"
    }
  ]
}
```

---

#### Get Product's Rentals

Retrieve all rental requests for a specific product.

**Endpoint**: `GET /products/{productId}/rentals`

**Authentication**: Required

**Headers**:

```http
Authorization: Bearer {token}
```

**Path Parameters**:

- `productId` (integer, required): Product ID

**Response** (200 OK):

```json
{
  "data": [
    {
      "id": 10,
      "renter": {
        "id": 1,
        "name": "John Doe",
        "email": "john.doe@example.com",
        "avatar_url": "http://localhost:8000/storage/avatars/1_abc123.jpg"
      },
      "start_date": "2025-12-10",
      "end_date": "2025-12-15",
      "total_price": "300.00",
      "status": "approved",
      "notes": "Need for weekend photoshoot event",
      "created_at": "2025-12-03T14:30:45.000000Z"
    }
  ]
}
```

---

### Purchases Endpoints

#### Create Purchase Request

Request to purchase a product.

**Endpoint**: `POST /purchases`

**Authentication**: Required

**Headers**:

```http
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body**:

```json
{
  "product_id": 25,
  "notes": "Interested in buying. When can I pick up?"
}
```

**Validation Rules**:

- `product_id`: required, exists:products,id
- `notes`: nullable, string, max:500

**Response** (201 Created):

```json
{
  "message": "Purchase request created successfully",
  "data": {
    "id": 5,
    "product": {
      "id": 25,
      "title": "Canon EOS R5 Camera",
      "thumbnail_url": "http://localhost:8000/storage/products/thumbnails/abc123.jpg",
      "sale_price": "2500.00",
      "owner": {
        "id": 5,
        "name": "Jane Smith",
        "avatar_url": "http://localhost:8000/storage/avatars/5_xyz789.jpg"
      }
    },
    "buyer": {
      "id": 1,
      "name": "John Doe",
      "email": "john.doe@example.com",
      "avatar_url": "http://localhost:8000/storage/avatars/1_abc123.jpg"
    },
    "purchase_price": "2500.00",
    "status": "pending",
    "notes": "Interested in buying. When can I pick up?",
    "created_at": "2025-12-03T14:30:45.000000Z"
  }
}
```

**Error Responses**:

*400 Bad Request* - Product not for sale

```json
{
  "message": "Product is not available for purchase."
}
```

*400 Bad Request* - Product not available

```json
{
  "message": "Product is no longer available."
}
```

*400 Bad Request* - Already sold

```json
{
  "message": "Product has already been sold."
}
```

---

#### Complete Purchase

Mark a purchase as completed. **Only product owner can complete**.

**Endpoint**: `PUT /purchases/{id}/complete`

**Authentication**: Required (Product owner only)

**Headers**:

```http
Authorization: Bearer {token}
```

**Path Parameters**:

- `id` (integer, required): Purchase ID

**Response** (200 OK):

```json
{
  "message": "Purchase completed successfully",
  "data": {
    "id": 5,
    "product": {
      "id": 25,
      "title": "Canon EOS R5 Camera",
      "owner": {
        "id": 5,
        "name": "Jane Smith"
      }
    },
    "buyer": {
      "id": 1,
      "name": "John Doe",
      "email": "john.doe@example.com"
    },
    "purchase_price": "2500.00",
    "status": "completed",
    "updated_at": "2025-12-03T16:30:45.000000Z"
  }
}
```

**Note**: When a purchase is completed, the product's `is_available` status is automatically set to `false`.

**Error Responses**:

*403 Forbidden* - Not product owner

```json
{
  "message": "This action is unauthorized."
}
```

---

#### Cancel Purchase

Cancel a purchase request. **Product owner or buyer can cancel**.

**Endpoint**: `PUT /purchases/{id}/cancel`

**Authentication**: Required (Product owner or buyer)

**Headers**:

```http
Authorization: Bearer {token}
```

**Path Parameters**:

- `id` (integer, required): Purchase ID

**Response** (200 OK):

```json
{
  "message": "Purchase cancelled successfully",
  "data": {
    "id": 5,
    "product": {
      "id": 25,
      "title": "Canon EOS R5 Camera"
    },
    "purchase_price": "2500.00",
    "status": "cancelled",
    "updated_at": "2025-12-03T16:30:45.000000Z"
  }
}
```

**Note**: When a purchase is cancelled, the product becomes available again (`is_available` set to `true`).

---

#### Get User's Purchases

Retrieve all purchase requests made by the authenticated user.

**Endpoint**: `GET /user/purchases`

**Authentication**: Required

**Headers**:

```http
Authorization: Bearer {token}
```

**Response** (200 OK):

```json
{
  "data": [
    {
      "id": 5,
      "product": {
        "id": 25,
        "title": "Canon EOS R5 Camera",
        "thumbnail_url": "http://localhost:8000/storage/products/thumbnails/abc123.jpg",
        "sale_price": "2500.00",
        "owner": {
          "id": 5,
          "name": "Jane Smith"
        }
      },
      "purchase_price": "2500.00",
      "status": "completed",
      "notes": "Interested in buying. When can I pick up?",
      "created_at": "2025-12-03T14:30:45.000000Z",
      "updated_at": "2025-12-03T16:30:45.000000Z"
    }
  ]
}
```

---

## Status Codes

The API uses standard HTTP status codes to indicate the success or failure of requests.

### Success Codes

| Code | Name | Description | Flutter Handling |
|------|------|-------------|------------------|
| 200 | OK | Request succeeded | Parse response data normally |
| 201 | Created | Resource created successfully | Parse response, navigate to new screen |
| 204 | No Content | Request succeeded, no content to return | Show success message, no data to parse |

### Client Error Codes

| Code | Name | Description | Flutter Handling |
|------|------|-------------|------------------|
| 400 | Bad Request | Invalid request or business logic error | Show error message from response |
| 401 | Unauthorized | Authentication required or token invalid | Redirect to login, clear stored token |
| 403 | Forbidden | Insufficient permissions | Show "Access Denied" message |
| 404 | Not Found | Resource not found | Show "Not Found" message or redirect |
| 422 | Unprocessable Entity | Validation failed | Display field-specific validation errors |
| 429 | Too Many Requests | Rate limit exceeded | Show retry message, implement backoff |

### Server Error Codes

| Code | Name | Description | Flutter Handling |
|------|------|-------------|------------------|
| 500 | Internal Server Error | Server error occurred | Show generic error, log for debugging |
| 503 | Service Unavailable | Server temporarily unavailable | Show maintenance message, retry later |

### Flutter Error Handling Example

```dart
Future<Map<String, dynamic>> handleApiResponse(http.Response response) async {
  switch (response.statusCode) {
    case 200:
    case 201:
      return {
        'success': true,
        'data': json.decode(response.body),
      };
      
    case 400:
      final error = json.decode(response.body);
      return {
        'success': false,
        'message': error['message'],
      };
      
    case 401:
      // Clear token and redirect to login
      await authService.deleteToken();
      return {
        'success': false,
        'message': 'Session expired. Please login again.',
        'shouldLogout': true,
      };
      
    case 403:
      return {
        'success': false,
        'message': 'Access denied. You don\'t have permission.',
      };
      
    case 404:
      return {
        'success': false,
        'message': 'Resource not found.',
      };
      
    case 422:
      final error = json.decode(response.body);
      return {
        'success': false,
        'message': error['message'],
        'errors': error['errors'] ?? {}, // Field-specific errors
      };
      
    case 429:
      return {
        'success': false,
        'message': 'Too many requests. Please try again later.',
        'shouldRetry': true,
      };
      
    case 500:
    case 503:
      return {
        'success': false,
        'message': 'Server error. Please try again later.',
      };
      
    default:
      return {
        'success': false,
        'message': 'An unexpected error occurred.',
      };
  }
}
```

---

## Verification Status Reference

### Status Values

| Status | Description | User Can Do | Admin Action Required |
|--------|-------------|-------------|----------------------|
| `unverified` | No verification documents submitted | Submit verification | N/A |
| `pending` | Documents under review | Wait for approval | Review and approve/reject |
| `verified` | User is verified | Create products, full access | N/A |
| `rejected` | Verification failed | Resubmit documents | N/A |

### Flutter Implementation for Verification Status

```dart
enum VerificationStatus {
  unverified,
  pending,
  verified,
  rejected,
}

class VerificationHelper {
  static VerificationStatus parseStatus(String status) {
    switch (status.toLowerCase()) {
      case 'unverified':
        return VerificationStatus.unverified;
      case 'pending':
        return VerificationStatus.pending;
      case 'verified':
        return VerificationStatus.verified;
      case 'rejected':
        return VerificationStatus.rejected;
      default:
        return VerificationStatus.unverified;
    }
  }

  static String getStatusMessage(VerificationStatus status) {
    switch (status) {
      case VerificationStatus.unverified:
        return 'Your account is not verified. Verify now to create products.';
      case VerificationStatus.pending:
        return 'Your verification is under review. This may take 24-48 hours.';
      case VerificationStatus.verified:
        return 'Your account is verified!';
      case VerificationStatus.rejected:
        return 'Your verification was rejected. Please submit new documents.';
    }
  }

  static Color getStatusColor(VerificationStatus status) {
    switch (status) {
      case VerificationStatus.unverified:
        return Colors.grey;
      case VerificationStatus.pending:
        return Colors.orange;
      case VerificationStatus.verified:
        return Colors.green;
      case VerificationStatus.rejected:
        return Colors.red;
    }
  }

  static IconData getStatusIcon(VerificationStatus status) {
    switch (status) {
      case VerificationStatus.unverified:
        return Icons.shield_outlined;
      case VerificationStatus.pending:
        return Icons.hourglass_empty;
      case VerificationStatus.verified:
        return Icons.verified_user;
      case VerificationStatus.rejected:
        return Icons.cancel;
    }
  }

  static bool canCreateProduct(VerificationStatus status) {
    return status == VerificationStatus.verified;
  }
}

// Usage in Widget
Widget buildVerificationBadge(String statusString) {
  final status = VerificationHelper.parseStatus(statusString);
  
  return Chip(
    avatar: Icon(
      VerificationHelper.getStatusIcon(status),
      color: Colors.white,
      size: 18,
    ),
    label: Text(
      statusString.toUpperCase(),
      style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
    ),
    backgroundColor: VerificationHelper.getStatusColor(status),
  );
}
```

---

## Important Notes for Flutter Developers

### 1. Image Upload Considerations

#### Maximum File Sizes
- **Avatars**: 2 MB per file
- **Product Images**: 2 MB per file, max 5 images
- **Verification Documents**: 5 MB per file, 3 files required (id_front, id_back, selfie)

#### Recommended Image Compression

```dart
import 'package:flutter_image_compress/flutter_image_compress.dart';

Future<File?> compressImage(File file) async {
  final dir = await getTemporaryDirectory();
  final targetPath = '${dir.path}/${DateTime.now().millisecondsSinceEpoch}.jpg';

  final result = await FlutterImageCompress.compressAndGetFile(
    file.absolute.path,
    targetPath,
    quality: 85,
    minWidth: 800,
    minHeight: 800,
  );

  return result != null ? File(result.path) : null;
}
```

### 2. Token Management

#### Secure Storage Implementation

```dart
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class TokenManager {
  static const _storage = FlutterSecureStorage();
  static const _tokenKey = 'auth_token';

  static Future<void> saveToken(String token) async {
    await _storage.write(key: _tokenKey, value: token);
  }

  static Future<String?> getToken() async {
    return await _storage.read(key: _tokenKey);
  }

  static Future<void> deleteToken() async {
    await _storage.delete(key: _tokenKey);
  }

  static Future<bool> hasToken() async {
    final token = await getToken();
    return token != null && token.isNotEmpty;
  }
}
```

### 3. Automatic Image Cleanup

**Important**: When updating products with new images or updating avatars, the API **automatically deletes old images**. Your Flutter app doesn't need to handle image deletion manually.

```dart
// When updating product with new images
final result = await productService.updateProduct(
  productId: 123,
  newImages: [newImage1, newImage2], // Old images automatically deleted
);

// When updating avatar
final result = await authService.uploadAvatar(newAvatarFile); // Old avatar deleted
```

### 4. Rate Limiting Handling

The verification image endpoint is rate-limited to **60 requests per minute**. Implement proper handling:

```dart
class RateLimitHelper {
  static const maxRetries = 3;
  static const retryDelay = Duration(seconds: 5);

  static Future<T?> executeWithRetry<T>(
    Future<T> Function() apiCall,
    {int retries = maxRetries}
  ) async {
    for (var i = 0; i < retries; i++) {
      try {
        return await apiCall();
      } catch (e) {
        if (e.toString().contains('429') && i < retries - 1) {
          await Future.delayed(retryDelay * (i + 1));
          continue;
        }
        rethrow;
      }
    }
    return null;
  }
}

// Usage
final imageBytes = await RateLimitHelper.executeWithRetry(
  () => verificationService.loadVerificationImage('id_front'),
);
```

### 5. Pagination Implementation

```dart
class PaginatedProductList extends StatefulWidget {
  @override
  _PaginatedProductListState createState() => _PaginatedProductListState();
}

class _PaginatedProductListState extends State<PaginatedProductList> {
  final _productService = ProductService();
  final _scrollController = ScrollController();
  
  List<dynamic> _products = [];
  int _currentPage = 1;
  int _lastPage = 1;
  bool _isLoading = false;
  bool _hasMore = true;

  @override
  void initState() {
    super.initState();
    _loadProducts();
    _scrollController.addListener(_onScroll);
  }

  void _onScroll() {
    if (_scrollController.position.pixels >=
        _scrollController.position.maxScrollExtent * 0.9) {
      if (!_isLoading && _hasMore) {
        _loadMoreProducts();
      }
    }
  }

  Future<void> _loadProducts() async {
    setState(() => _isLoading = true);

    final result = await _productService.getProducts(page: 1);

    if (result['success']) {
      setState(() {
        _products = result['products'];
        _currentPage = result['meta']['current_page'];
        _lastPage = result['meta']['last_page'];
        _hasMore = _currentPage < _lastPage;
        _isLoading = false;
      });
    }
  }

  Future<void> _loadMoreProducts() async {
    if (_currentPage >= _lastPage) return;

    setState(() => _isLoading = true);
    
    final result = await _productService.getProducts(page: _currentPage + 1);

    if (result['success']) {
      setState(() {
        _products.addAll(result['products']);
        _currentPage = result['meta']['current_page'];
        _hasMore = _currentPage < _lastPage;
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return ListView.builder(
      controller: _scrollController,
      itemCount: _products.length + (_hasMore ? 1 : 0),
      itemBuilder: (context, index) {
        if (index == _products.length) {
          return Center(child: CircularProgressIndicator());
        }
        return ProductCard(product: _products[index]);
      },
    );
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }
}
```

### 6. Cached Network Images

Use `cached_network_image` for efficient image loading:

```dart
import 'package:cached_network_image/cached_network_image.dart';

Widget buildProductImage(String imageUrl) {
  return CachedNetworkImage(
    imageUrl: imageUrl,
    placeholder: (context, url) => Container(
      color: Colors.grey[200],
      child: Center(child: CircularProgressIndicator()),
    ),
    errorWidget: (context, url, error) => Container(
      color: Colors.grey[300],
      child: Icon(Icons.error, color: Colors.red),
    ),
    fit: BoxFit.cover,
    memCacheWidth: 800, // Cache at specific width
    memCacheHeight: 800,
  );
}
```

### 7. Form Validation

```dart
class ProductFormValidator {
  static String? validateTitle(String? value) {
    if (value == null || value.isEmpty) {
      return 'Title is required';
    }
    if (value.length > 255) {
      return 'Title must be 255 characters or less';
    }
    return null;
  }

  static String? validatePrice(String? value) {
    if (value == null || value.isEmpty) {
      return 'Price is required';
    }
    final price = double.tryParse(value);
    if (price == null) {
      return 'Please enter a valid number';
    }
    if (price < 1) {
      return 'Price must be at least \$1';
    }
    return null;
  }

  static String? validateImages(List<File>? images) {
    if (images == null || images.isEmpty) {
      return 'At least one image is required';
    }
    if (images.length > 5) {
      return 'Maximum 5 images allowed';
    }
    return null;
  }
}
```

### 8. Loading States Management

```dart
enum LoadingState {
  initial,
  loading,
  loaded,
  error,
}

class ProductProvider extends ChangeNotifier {
  LoadingState _state = LoadingState.initial;
  List<dynamic> _products = [];
  String? _errorMessage;

  LoadingState get state => _state;
  List<dynamic> get products => _products;
  String? get errorMessage => _errorMessage;

  Future<void> loadProducts() async {
    _state = LoadingState.loading;
    notifyListeners();

    final result = await ProductService().getProducts();

    if (result['success']) {
      _products = result['products'];
      _state = LoadingState.loaded;
      _errorMessage = null;
    } else {
      _state = LoadingState.error;
      _errorMessage = result['message'];
    }

    notifyListeners();
  }
}

// Usage in Widget
Widget build(BuildContext context) {
  return Consumer<ProductProvider>(
    builder: (context, provider, child) {
      switch (provider.state) {
        case LoadingState.loading:
          return Center(child: CircularProgressIndicator());
        case LoadingState.error:
          return Center(child: Text(provider.errorMessage ?? 'Error'));
        case LoadingState.loaded:
          return ProductList(products: provider.products);
        default:
          return SizedBox();
      }
    },
  );
}
```

### 9. Environment Configuration

```dart
// lib/config/environment.dart
class Environment {
  static const bool isProduction = bool.fromEnvironment('dart.vm.product');
  
  static String get apiBaseUrl {
    return isProduction
        ? 'https://api.rentedmarketplace.com/api/v1'
        : 'http://localhost:8000/api/v1';
  }

  static String get storageUrl {
    return isProduction
        ? 'https://api.rentedmarketplace.com/storage'
        : 'http://localhost:8000/storage';
  }
}
```

### 10. Testing API Integration

```dart
// test/services/auth_service_test.dart
import 'package:flutter_test/flutter_test.dart';
import 'package:mockito/mockito.dart';
import 'package:http/http.dart' as http;

void main() {
  group('AuthService Tests', () {
    late AuthService authService;
    late MockClient mockClient;

    setUp(() {
      mockClient = MockClient();
      authService = AuthService(client: mockClient);
    });

    test('login returns user data on success', () async {
      when(mockClient.post(
        any,
        headers: anyNamed('headers'),
        body: anyNamed('body'),
      )).thenAnswer((_) async => http.Response(
        '{"token": "abc123", "user": {"id": 1, "name": "Test User"}}',
        200,
      ));

      final result = await authService.login(
        email: 'test@example.com',
        password: 'password123',
      );

      expect(result['success'], true);
      expect(result['user']['name'], 'Test User');
    });

    test('login handles 422 validation error', () async {
      when(mockClient.post(
        any,
        headers: anyNamed('headers'),
        body: anyNamed('body'),
      )).thenAnswer((_) async => http.Response(
        '{"message": "Invalid credentials", "errors": {"email": ["Wrong email"]}}',
        422,
      ));

      final result = await authService.login(
        email: 'wrong@example.com',
        password: 'wrongpass',
      );

      expect(result['success'], false);
      expect(result['message'], 'Invalid credentials');
    });
  });
}
```

---

## Status Codes

The API uses standard HTTP status codes to indicate the success or failure of requests.

### Success Codes

| Code | Name | Description |
|------|------|-------------|
| 200 | OK | Request succeeded |
| 201 | Created | Resource created successfully |
| 204 | No Content | Request succeeded, no content to return |

### Client Error Codes

| Code | Name | Description |
|------|------|-------------|
| 400 | Bad Request | Invalid request or business logic error |
| 401 | Unauthorized | Authentication required or token invalid |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation failed |
| 429 | Too Many Requests | Rate limit exceeded |

### Server Error Codes

| Code | Name | Description |
|------|------|-------------|
| 500 | Internal Server Error | Server error occurred |
| 503 | Service Unavailable | Server temporarily unavailable |

---

## Pagination

Endpoints that return multiple items use cursor-based pagination.

### Pagination Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `page` | integer | 1 | Current page number |
| `per_page` | integer | 15 | Items per page (max: 100) |

### Example Request

```
GET /products?page=2&per_page=20
```

### Pagination Response Structure

```json
{
  "data": [...],
  "links": {
    "first": "http://api.example.com/products?page=1",
    "last": "http://api.example.com/products?page=10",
    "prev": "http://api.example.com/products?page=1",
    "next": "http://api.example.com/products?page=3"
  },
  "meta": {
    "current_page": 2,
    "from": 16,
    "last_page": 10,
    "per_page": 20,
    "to": 35,
    "total": 200
  }
}
```

---

## File Uploads

### Supported File Types

**Images** (Products & Avatars):

- JPEG (.jpg, .jpeg)
- PNG (.png)
- WebP (.webp) - Products only

**Documents** (Verification):

- JPEG (.jpg, .jpeg)
- PNG (.png)
- PDF (.pdf)

### File Size Limits

- **Avatar**: 2 MB, dimensions 100x100 to 2000x2000 pixels
- **Product Images**: 2 MB per file (thumbnail + up to 5 additional images)
- **Verification Documents**: 5 MB per file

### Upload Format

Use `multipart/form-data` content type for file uploads.

### Example (cURL) - Product with Images

```bash
curl -X POST "http://localhost:8000/api/v1/products" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -F "category_id=2" \
  -F "title=Camera Equipment" \
  -F "description=Professional camera" \
  -F "price_per_day=50" \
  -F "thumbnail=@/path/to/thumbnail.jpg" \
  -F "images[]=@/path/to/image1.jpg" \
  -F "images[]=@/path/to/image2.jpg"
```

### Example (cURL) - Avatar Upload

```bash
curl -X POST "http://localhost:8000/api/v1/user/avatar" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -F "avatar=@/path/to/avatar.jpg"
```

---

## Flutter SDK Implementation Guide

### Setup and Configuration

#### 1. Add Dependencies to `pubspec.yaml`

```yaml
dependencies:
  flutter:
    sdk: flutter
  http: ^1.1.0
  shared_preferences: ^2.2.2
  flutter_secure_storage: ^9.0.0
  image_picker: ^1.0.7
  cached_network_image: ^3.3.1
  dio: ^5.4.0  # Alternative to http for better features

dev_dependencies:
  flutter_test:
    sdk: flutter
```

#### 2. Create API Service Base Class

```dart
// lib/services/api_service.dart
import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class ApiService {
  static const String baseUrl = 'http://localhost:8000/api/v1';
  static const String productionUrl = 'https://api.rentedmarketplace.com/api/v1';
  
  final storage = const FlutterSecureStorage();
  String? _token;

  // Get current environment URL
  String get apiUrl => baseUrl; // Change to productionUrl for production

  // Initialize token from storage
  Future<void> init() async {
    _token = await storage.read(key: 'auth_token');
  }

  // Get authorization headers
  Map<String, String> get headers => {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    if (_token != null) 'Authorization': 'Bearer $_token',
  };

  // Get authorization headers for multipart
  Map<String, String> get multipartHeaders => {
    'Accept': 'application/json',
    if (_token != null) 'Authorization': 'Bearer $_token',
  };

  // Save token to secure storage
  Future<void> saveToken(String token) async {
    _token = token;
    await storage.write(key: 'auth_token', value: token);
  }

  // Delete token
  Future<void> deleteToken() async {
    _token = null;
    await storage.delete(key: 'auth_token');
  }

  // Check if user is authenticated
  bool get isAuthenticated => _token != null;

  // Handle API errors
  Map<String, dynamic> handleError(http.Response response) {
    final body = json.decode(response.body);
    return {
      'success': false,
      'message': body['message'] ?? 'An error occurred',
      'errors': body['errors'] ?? {},
      'statusCode': response.statusCode,
    };
  }
}
```

#### 3. Create Authentication Service

```dart
// lib/services/auth_service.dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'api_service.dart';

class AuthService extends ApiService {
  // Register new user
  Future<Map<String, dynamic>> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$apiUrl/register'),
        headers: headers,
        body: json.encode({
          'name': name,
          'email': email,
          'password': password,
          'password_confirmation': passwordConfirmation,
        }),
      );

      if (response.statusCode == 201) {
        final data = json.decode(response.body);
        await saveToken(data['token']);
        return {
          'success': true,
          'message': data['message'],
          'user': data['user'],
        };
      }

      return handleError(response);
    } catch (e) {
      return {
        'success': false,
        'message': 'Network error: $e',
      };
    }
  }

  // Login user
  Future<Map<String, dynamic>> login({
    required String email,
    required String password,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$apiUrl/login'),
        headers: headers,
        body: json.encode({
          'email': email,
          'password': password,
        }),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        await saveToken(data['token']);
        return {
          'success': true,
          'message': data['message'],
          'user': data['user'],
        };
      }

      return handleError(response);
    } catch (e) {
      return {
        'success': false,
        'message': 'Network error: $e',
      };
    }
  }

  // Logout user
  Future<Map<String, dynamic>> logout() async {
    try {
      await init(); // Ensure token is loaded
      
      final response = await http.post(
        Uri.parse('$apiUrl/logout'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        await deleteToken();
        return {
          'success': true,
          'message': json.decode(response.body)['message'],
        };
      }

      return handleError(response);
    } catch (e) {
      return {
        'success': false,
        'message': 'Network error: $e',
      };
    }
  }

  // Get current user
  Future<Map<String, dynamic>> getCurrentUser() async {
    try {
      await init();
      
      final response = await http.get(
        Uri.parse('$apiUrl/user'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return {
          'success': true,
          'user': data['data'],
        };
      }

      return handleError(response);
    } catch (e) {
      return {
        'success': false,
        'message': 'Network error: $e',
      };
    }
  }

  // Update user profile
  Future<Map<String, dynamic>> updateProfile({
    String? name,
    String? email,
    String? currentPassword,
    String? password,
    String? passwordConfirmation,
  }) async {
    try {
      await init();
      
      final body = <String, dynamic>{};
      if (name != null) body['name'] = name;
      if (email != null) body['email'] = email;
      if (currentPassword != null) body['current_password'] = currentPassword;
      if (password != null) body['password'] = password;
      if (passwordConfirmation != null) body['password_confirmation'] = passwordConfirmation;

      final response = await http.put(
        Uri.parse('$apiUrl/user/profile'),
        headers: headers,
        body: json.encode(body),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return {
          'success': true,
          'message': data['message'],
          'user': data['data'],
        };
      }

      return handleError(response);
    } catch (e) {
      return {
        'success': false,
        'message': 'Network error: $e',
      };
    }
  }

  // Upload/Update Avatar
  Future<Map<String, dynamic>> uploadAvatar(File imageFile) async {
    try {
      await init();
      
      var request = http.MultipartRequest(
        'POST',
        Uri.parse('$apiUrl/user/avatar'),
      );

      request.headers.addAll(multipartHeaders);
      request.files.add(await http.MultipartFile.fromPath(
        'avatar',
        imageFile.path,
      ));

      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return {
          'success': true,
          'message': data['message'],
          'user': data['data'],
        };
      }

      return handleError(response);
    } catch (e) {
      return {
        'success': false,
        'message': 'Network error: $e',
      };
    }
  }

  // Delete Avatar
  Future<Map<String, dynamic>> deleteAvatar() async {
    try {
      await init();
      
      final response = await http.delete(
        Uri.parse('$apiUrl/user/avatar'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return {
          'success': true,
          'message': data['message'],
          'user': data['data'],
        };
      }

      return handleError(response);
    } catch (e) {
      return {
        'success': false,
        'message': 'Network error: $e',
      };
    }
  }
}
```

#### 4. Create Verification Service

```dart
// lib/services/verification_service.dart
import 'dart:convert';
import 'dart:io';
import 'dart:typed_data';
import 'package:http/http.dart' as http;
import 'api_service.dart';

class VerificationService extends ApiService {
  // Upload verification documents
  Future<Map<String, dynamic>> uploadDocuments({
    required File idFront,
    required File idBack,
    required File selfie,
    String? documentType,
  }) async {
    try {
      await init();
      
      var request = http.MultipartRequest(
        'POST',
        Uri.parse('$apiUrl/verify'),
      );

      request.headers.addAll(multipartHeaders);
      
      request.files.add(await http.MultipartFile.fromPath('id_front', idFront.path));
      request.files.add(await http.MultipartFile.fromPath('id_back', idBack.path));
      request.files.add(await http.MultipartFile.fromPath('selfie', selfie.path));
      
      if (documentType != null) {
        request.fields['document_type'] = documentType;
      }

      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);

      if (response.statusCode == 201) {
        final data = json.decode(response.body);
        return {
          'success': true,
          'message': data['message'],
          'verification': data['data'],
        };
      }

      return handleError(response);
    } catch (e) {
      return {
        'success': false,
        'message': 'Network error: $e',
      };
    }
  }

  // Get verification status
  Future<Map<String, dynamic>> getStatus() async {
    try {
      await init();
      
      final response = await http.get(
        Uri.parse('$apiUrl/verify/status'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return {
          'success': true,
          'verification': data['data'],
        };
      }

      return handleError(response);
    } catch (e) {
      return {
        'success': false,
        'message': 'Network error: $e',
      };
    }
  }

  // Load verification image securely (owner only)
  Future<Uint8List?> loadVerificationImage(String imageType) async {
    try {
      await init();
      
      // Validate image type
      if (!['id_front', 'id_back', 'selfie'].contains(imageType)) {
        throw Exception('Invalid image type');
      }

      final response = await http.get(
        Uri.parse('$apiUrl/verify/image/$imageType'),
        headers: multipartHeaders,
      );

      if (response.statusCode == 200) {
        return response.bodyBytes;
      } else if (response.statusCode == 404) {
        return null; // Image not found
      } else if (response.statusCode == 429) {
        throw Exception('Rate limit exceeded. Please try again later.');
      } else if (response.statusCode == 403) {
        throw Exception('Unauthorized access');
      }

      throw Exception('Failed to load image');
    } catch (e) {
      throw Exception('Error loading image: $e');
    }
  }
}
```

#### 5. Create Product Service

```dart
// lib/services/product_service.dart
import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'api_service.dart';

class ProductService extends ApiService {
  // Get all products (public)
  Future<Map<String, dynamic>> getProducts({int page = 1, int perPage = 15}) async {
    try {
      final response = await http.get(
        Uri.parse('$apiUrl/products?page=$page&per_page=$perPage'),
        headers: {'Accept': 'application/json'},
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return {
          'success': true,
          'products': data['data'],
          'meta': data['meta'],
        };
      }

      return handleError(response);
    } catch (e) {
      return {
        'success': false,
        'message': 'Network error: $e',
      };
    }
  }

  // Get single product (public)
  Future<Map<String, dynamic>> getProduct(int id) async {
    try {
      final response = await http.get(
        Uri.parse('$apiUrl/products/$id'),
        headers: {'Accept': 'application/json'},
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return {
          'success': true,
          'product': data['data'],
        };
      }

      return handleError(response);
    } catch (e) {
      return {
        'success': false,
        'message': 'Network error: $e',
      };
    }
  }

  // Create product (requires verification)
  Future<Map<String, dynamic>> createProduct({
    required int categoryId,
    required String title,
    required String description,
    required double pricePerDay,
    bool isForSale = false,
    double? salePrice,
    List<File>? images,
  }) async {
    try {
      await init();
      
      var request = http.MultipartRequest(
        'POST',
        Uri.parse('$apiUrl/products'),
      );

      request.headers.addAll(multipartHeaders);
      
      request.fields['category_id'] = categoryId.toString();
      request.fields['title'] = title;
      request.fields['description'] = description;
      request.fields['price_per_day'] = pricePerDay.toString();
      request.fields['is_for_sale'] = isForSale ? '1' : '0';
      
      if (isForSale && salePrice != null) {
        request.fields['sale_price'] = salePrice.toString();
      }

      // Add images (max 5)
      if (images != null && images.isNotEmpty) {
        for (var i = 0; i < images.length && i < 5; i++) {
          request.files.add(await http.MultipartFile.fromPath(
            'images[]',
            images[i].path,
          ));
        }
      }

      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);

      if (response.statusCode == 201) {
        final data = json.decode(response.body);
        return {
          'success': true,
          'message': data['message'],
          'product': data['data'],
        };
      }

      return handleError(response);
    } catch (e) {
      return {
        'success': false,
        'message': 'Network error: $e',
      };
    }
  }

  // Update product (owner only)
  Future<Map<String, dynamic>> updateProduct({
    required int productId,
    String? title,
    String? description,
    double? pricePerDay,
    bool? isAvailable,
    List<File>? newImages,
  }) async {
    try {
      await init();

      // Text-only update
      if (newImages == null || newImages.isEmpty) {
        final body = <String, dynamic>{};
        if (title != null) body['title'] = title;
        if (description != null) body['description'] = description;
        if (pricePerDay != null) body['price_per_day'] = pricePerDay;
        if (isAvailable != null) body['is_available'] = isAvailable;

        final response = await http.put(
          Uri.parse('$apiUrl/products/$productId'),
          headers: headers,
          body: json.encode(body),
        );

        if (response.statusCode == 200) {
          final data = json.decode(response.body);
          return {
            'success': true,
            'message': data['message'],
            'product': data['data'],
          };
        }

        return handleError(response);
      }

      // Update with images
      var request = http.MultipartRequest(
        'POST',
        Uri.parse('$apiUrl/products/$productId'),
      );

      request.headers.addAll(multipartHeaders);
      request.fields['_method'] = 'PUT';
      
      if (title != null) request.fields['title'] = title;
      if (description != null) request.fields['description'] = description;
      if (pricePerDay != null) request.fields['price_per_day'] = pricePerDay.toString();
      if (isAvailable != null) request.fields['is_available'] = isAvailable ? '1' : '0';

      // Add new images
      for (var i = 0; i < newImages.length && i < 5; i++) {
        request.files.add(await http.MultipartFile.fromPath(
          'images[]',
          newImages[i].path,
        ));
      }

      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return {
          'success': true,
          'message': data['message'],
          'product': data['data'],
        };
      }

      return handleError(response);
    } catch (e) {
      return {
        'success': false,
        'message': 'Network error: $e',
      };
    }
  }

  // Delete product (owner only)
  Future<Map<String, dynamic>> deleteProduct(int productId) async {
    try {
      await init();
      
      final response = await http.delete(
        Uri.parse('$apiUrl/products/$productId'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return {
          'success': true,
          'message': data['message'],
        };
      }

      return handleError(response);
    } catch (e) {
      return {
        'success': false,
        'message': 'Network error: $e',
      };
    }
  }

  // Get user's products
  Future<Map<String, dynamic>> getUserProducts() async {
    try {
      await init();
      
      final response = await http.get(
        Uri.parse('$apiUrl/user/products'),
        headers: headers,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return {
          'success': true,
          'products': data['data'],
        };
      }

      return handleError(response);
    } catch (e) {
      return {
        'success': false,
        'message': 'Network error: $e',
      };
    }
  }
}
```

#### 6. Create Category Service

```dart
// lib/services/category_service.dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'api_service.dart';

class CategoryService extends ApiService {
  // Get all categories (public)
  Future<Map<String, dynamic>> getCategories() async {
    try {
      final response = await http.get(
        Uri.parse('$apiUrl/categories'),
        headers: {'Accept': 'application/json'},
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return {
          'success': true,
          'categories': data['data'],
        };
      }

      return handleError(response);
    } catch (e) {
      return {
        'success': false,
        'message': 'Network error: $e',
      };
    }
  }

  // Get single category (public)
  Future<Map<String, dynamic>> getCategory(int id) async {
    try {
      final response = await http.get(
        Uri.parse('$apiUrl/categories/$id'),
        headers: {'Accept': 'application/json'},
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return {
          'success': true,
          'category': data['data'],
        };
      }

      return handleError(response);
    } catch (e) {
      return {
        'success': false,
        'message': 'Network error: $e',
      };
    }
  }
}
```

### Usage Examples in Flutter Widgets

#### Login Screen Example

```dart
// lib/screens/login_screen.dart
import 'package:flutter/material.dart';
import '../services/auth_service.dart';

class LoginScreen extends StatefulWidget {
  @override
  _LoginScreenState createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _authService = AuthService();
  bool _isLoading = false;

  Future<void> _login() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);

    final result = await _authService.login(
      email: _emailController.text,
      password: _passwordController.text,
    );

    setState(() => _isLoading = false);

    if (result['success']) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(result['message'])),
      );
      // Navigate to home screen
      Navigator.pushReplacementNamed(context, '/home');
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result['message']),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Login')),
      body: Padding(
        padding: EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            children: [
              TextFormField(
                controller: _emailController,
                decoration: InputDecoration(labelText: 'Email'),
                keyboardType: TextInputType.emailAddress,
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Please enter your email';
                  }
                  return null;
                },
              ),
              SizedBox(height: 16),
              TextFormField(
                controller: _passwordController,
                decoration: InputDecoration(labelText: 'Password'),
                obscureText: true,
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Please enter your password';
                  }
                  return null;
                },
              ),
              SizedBox(height: 24),
              _isLoading
                  ? CircularProgressIndicator()
                  : ElevatedButton(
                      onPressed: _login,
                      child: Text('Login'),
                    ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }
}
```

#### Product List Screen Example

```dart
// lib/screens/product_list_screen.dart
import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../services/product_service.dart';

class ProductListScreen extends StatefulWidget {
  @override
  _ProductListScreenState createState() => _ProductListScreenState();
}

class _ProductListScreenState extends State<ProductListScreen> {
  final _productService = ProductService();
  List<dynamic> _products = [];
  bool _isLoading = true;
  int _currentPage = 1;

  @override
  void initState() {
    super.initState();
    _loadProducts();
  }

  Future<void> _loadProducts() async {
    setState(() => _isLoading = true);

    final result = await _productService.getProducts(page: _currentPage);

    if (result['success']) {
      setState(() {
        _products = result['products'];
        _isLoading = false;
      });
    } else {
      setState(() => _isLoading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(result['message'])),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Products')),
      body: _isLoading
          ? Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadProducts,
              child: ListView.builder(
                itemCount: _products.length,
                itemBuilder: (context, index) {
                  final product = _products[index];
                  return Card(
                    margin: EdgeInsets.all(8),
                    child: ListTile(
                      leading: product['image_urls'] != null &&
                              product['image_urls'].isNotEmpty
                          ? CachedNetworkImage(
                              imageUrl: product['image_urls'][0],
                              width: 60,
                              height: 60,
                              fit: BoxFit.cover,
                              placeholder: (context, url) =>
                                  CircularProgressIndicator(),
                              errorWidget: (context, url, error) =>
                                  Icon(Icons.error),
                            )
                          : Icon(Icons.image_not_supported, size: 60),
                      title: Text(product['title']),
                      subtitle: Text('\$${product['price_per_day']}/day'),
                      trailing: product['is_for_sale']
                          ? Chip(label: Text('For Sale'))
                          : null,
                      onTap: () {
                        // Navigate to product details
                      },
                    ),
                  );
                },
              ),
            ),
    );
  }
}
```

#### Verification Upload Screen Example

```dart
// lib/screens/verification_upload_screen.dart
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../services/verification_service.dart';

class VerificationUploadScreen extends StatefulWidget {
  @override
  _VerificationUploadScreenState createState() =>
      _VerificationUploadScreenState();
}

class _VerificationUploadScreenState extends State<VerificationUploadScreen> {
  final _verificationService = VerificationService();
  final _picker = ImagePicker();
  
  File? _idFront;
  File? _idBack;
  File? _selfie;
  String? _documentType = 'national_id';
  bool _isLoading = false;

  Future<void> _pickImage(String type) async {
    final pickedFile = await _picker.pickImage(
      source: ImageSource.camera,
      maxWidth: 1920,
      maxHeight: 1080,
      imageQuality: 85,
    );

    if (pickedFile != null) {
      setState(() {
        switch (type) {
          case 'id_front':
            _idFront = File(pickedFile.path);
            break;
          case 'id_back':
            _idBack = File(pickedFile.path);
            break;
          case 'selfie':
            _selfie = File(pickedFile.path);
            break;
        }
      });
    }
  }

  Future<void> _uploadDocuments() async {
    if (_idFront == null || _idBack == null || _selfie == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Please upload all required documents')),
      );
      return;
    }

    setState(() => _isLoading = true);

    final result = await _verificationService.uploadDocuments(
      idFront: _idFront!,
      idBack: _idBack!,
      selfie: _selfie!,
      documentType: _documentType,
    );

    setState(() => _isLoading = false);

    if (result['success']) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(result['message'])),
      );
      Navigator.pop(context);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result['message']),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Verify Account')),
      body: SingleChildScrollView(
        padding: EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text(
              'Upload Verification Documents',
              style: Theme.of(context).textTheme.headlineSmall,
            ),
            SizedBox(height: 16),
            
            // Document Type Dropdown
            DropdownButtonFormField<String>(
              value: _documentType,
              decoration: InputDecoration(labelText: 'Document Type'),
              items: [
                DropdownMenuItem(value: 'national_id', child: Text('National ID')),
                DropdownMenuItem(value: 'passport', child: Text('Passport')),
                DropdownMenuItem(value: 'driver_license', child: Text('Driver License')),
              ],
              onChanged: (value) => setState(() => _documentType = value),
            ),
            SizedBox(height: 24),
            
            // ID Front
            _buildImagePicker('ID Front', _idFront, 'id_front'),
            SizedBox(height: 16),
            
            // ID Back
            _buildImagePicker('ID Back', _idBack, 'id_back'),
            SizedBox(height: 16),
            
            // Selfie
            _buildImagePicker('Selfie with ID', _selfie, 'selfie'),
            SizedBox(height: 24),
            
            // Upload Button
            _isLoading
                ? Center(child: CircularProgressIndicator())
                : ElevatedButton(
                    onPressed: _uploadDocuments,
                    child: Text('Upload Documents'),
                  ),
          ],
        ),
      ),
    );
  }

  Widget _buildImagePicker(String label, File? image, String type) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: TextStyle(fontWeight: FontWeight.bold)),
        SizedBox(height: 8),
        GestureDetector(
          onTap: () => _pickImage(type),
          child: Container(
            height: 150,
            decoration: BoxDecoration(
              border: Border.all(color: Colors.grey),
              borderRadius: BorderRadius.circular(8),
            ),
            child: image == null
                ? Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.camera_alt, size: 48, color: Colors.grey),
                        SizedBox(height: 8),
                        Text('Tap to capture', style: TextStyle(color: Colors.grey)),
                      ],
                    ),
                  )
                : ClipRRect(
                    borderRadius: BorderRadius.circular(8),
                    child: Image.file(image, fit: BoxFit.cover),
                  ),
          ),
        ),
      ],
    );
  }
}
```

### Best Practices for Flutter Development

1. **Error Handling**: Always wrap API calls in try-catch blocks
2. **Loading States**: Show loading indicators during API calls
3. **Token Management**: Use flutter_secure_storage for secure token storage
4. **Image Caching**: Use cached_network_image for product and avatar images
5. **Offline Support**: Consider implementing local caching with sqflite
6. **Form Validation**: Validate all user inputs before API submission
7. **Rate Limiting**: Handle 429 responses gracefully with retry logic
8. **Security**: Never log sensitive data (tokens, passwords) in production
9. **Image Optimization**: Compress images before upload to save bandwidth
10. **User Feedback**: Always show success/error messages to users

---

## Best Practices

### For Mobile Developers

1. **Store Tokens Securely**
   - Use secure storage (Keychain on iOS, Keystore on Android)
   - Never store tokens in plain text
   - Clear tokens on logout

2. **Handle Token Expiration**
   - Implement automatic token refresh if needed
   - Redirect to login on 401 responses
   - Store refresh token securely if implemented

3. **Error Handling**
   - Always check response status codes
   - Display user-friendly error messages
   - Handle validation errors field by field
   - Implement retry logic for network failures

4. **Optimize Network Usage**
   - Cache responses when appropriate
   - Use pagination efficiently
   - Implement pull-to-refresh
   - Handle offline scenarios gracefully

5. **File Uploads**
   - Show upload progress to users
   - Validate file types and sizes before upload
   - Compress images before upload when possible
   - Handle upload failures with retry option
   - Use image caching for avatars and product images

6. **Performance**
   - Implement lazy loading for lists
   - Use image caching libraries
   - Minimize API calls
   - Prefetch data when appropriate
   - Cache user avatar and product images locally

7. **User Experience**
   - Show loading indicators during API calls
   - Implement optimistic UI updates
   - Provide clear feedback on actions
   - Handle edge cases gracefully
   - Show image placeholders while loading

### API Request Examples

#### Swift (iOS) - Login

```swift
func loginUser(email: String, password: String) {
    let url = URL(string: "http://localhost:8000/api/v1/login")!
    var request = URLRequest(url: url)
    request.httpMethod = "POST"
    request.setValue("application/json", forHTTPHeaderField: "Content-Type")
    request.setValue("application/json", forHTTPHeaderField: "Accept")
    
    let body: [String: Any] = [
        "email": email,
        "password": password
    ]
    
    request.httpBody = try? JSONSerialization.data(withJSONObject: body)
    
    URLSession.shared.dataTask(with: request) { data, response, error in
        guard let data = data, error == nil else {
            print("Network error: \(error?.localizedDescription ?? "Unknown")")
            return
        }
        
        if let httpResponse = response as? HTTPURLResponse {
            if httpResponse.statusCode == 200 {
                // Parse successful login response
                if let json = try? JSONSerialization.jsonObject(with: data) as? [String: Any] {
                    let token = json["token"] as? String
                    let user = json["user"] as? [String: Any]
                    // Store token securely in Keychain
                }
            } else {
                // Handle error response
            }
        }
    }.resume()
}
```

#### Swift (iOS) - Upload Avatar

```swift
func uploadAvatar(image: UIImage) {
    guard let imageData = image.jpegData(compressionQuality: 0.8) else { return }
    
    let url = URL(string: "http://localhost:8000/api/v1/user/avatar")!
    var request = URLRequest(url: url)
    request.httpMethod = "POST"
    request.setValue("Bearer \(token)", forHTTPHeaderField: "Authorization")
    
    let boundary = UUID().uuidString
    request.setValue("multipart/form-data; boundary=\(boundary)", forHTTPHeaderField: "Content-Type")
    
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

#### Kotlin (Android) - Login

```kotlin
fun loginUser(email: String, password: String) {
    val client = OkHttpClient()
    val json = JSONObject()
    json.put("email", email)
    json.put("password", password)
    
    val body = RequestBody.create(
        "application/json".toMediaType(),
        json.toString()
    )
    
    val request = Request.Builder()
        .url("http://localhost:8000/api/v1/login")
        .post(body)
        .addHeader("Accept", "application/json")
        .build()
    
    client.newCall(request).enqueue(object : Callback {
        override fun onResponse(call: Call, response: Response) {
            if (response.isSuccessful) {
                val jsonResponse = JSONObject(response.body?.string() ?: "")
                val token = jsonResponse.getString("token")
                val user = jsonResponse.getJSONObject("user")
                // Store token securely in Android Keystore
            } else {
                // Handle error
            }
        }
        
        override fun onFailure(call: Call, e: IOException) {
            // Handle network failure
        }
    })
}
```

#### Kotlin (Android) - Upload Avatar

```kotlin
fun uploadAvatar(imageFile: File, token: String) {
    val client = OkHttpClient()
    
    val requestBody = MultipartBody.Builder()
        .setType(MultipartBody.FORM)
        .addFormDataPart(
            "avatar",
            imageFile.name,
            imageFile.asRequestBody("image/jpeg".toMediaType())
        )
        .build()
    
    val request = Request.Builder()
        .url("http://localhost:8000/api/v1/user/avatar")
        .post(requestBody)
        .addHeader("Authorization", "Bearer $token")
        .addHeader("Accept", "application/json")
        .build()
    
    client.newCall(request).enqueue(object : Callback {
        override fun onResponse(call: Call, response: Response) {
            if (response.isSuccessful) {
                val jsonResponse = JSONObject(response.body?.string() ?: "")
                val user = jsonResponse.getJSONObject("data")
                val avatarUrl = user.getString("avatar_url")
                // Update UI with new avatar
            }
        }
        
        override fun onFailure(call: Call, e: IOException) {
            // Handle error
        }
    })
}
```

---

## Support & Contact

### API Documentation

- **Postman Collection**: [POSTMAN_TESTING_GUIDE.md](./POSTMAN_TESTING_GUIDE.md)
- **Docker Guide**: [DOCKER_IMPLEMENTATION_GUIDE.md](./DOCKER_IMPLEMENTATION_GUIDE.md)
- **Avatar Feature**: [AVATAR_UPLOAD_FEATURE.md](./AVATAR_UPLOAD_FEATURE.md)
- **Product Images**: [PRODUCT_IMAGE_UPLOAD_FEATURE.md](./PRODUCT_IMAGE_UPLOAD_FEATURE.md)
- **Test Results**: [TEST_RESULTS.md](./TEST_RESULTS.md)

### Technology Stack

- **Framework**: Laravel 12
- **PHP**: 8.4+
- **Database**: PostgreSQL 16
- **Cache**: Redis 7
- **Performance**: Laravel Octane with Swoole
- **Authentication**: Laravel Sanctum 4
- **Containerization**: Docker with Docker Compose

### Development Team

- **API Version**: v1
- **Last Updated**: December 4, 2025

### Reporting Issues

- Check existing documentation first
- Provide clear reproduction steps
- Include request/response examples
- Specify environment (development/production)

---

**Note**: This documentation is continuously updated as new features are added to the API. Always refer to the latest version for accurate information.
