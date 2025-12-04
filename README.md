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

Upload identification documents for user verification.

**Endpoint**: `POST /verify`

**Authentication**: Required

**Headers**:

```http
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body** (multipart/form-data):

- `id_front` (file, required): Front of ID document (JPEG, JPG, PNG, PDF)
- `id_back` (file, required): Back of ID document (JPEG, JPG, PNG, PDF)
- `document_type` (string, optional): Type of document (passport, national_id, driver_license)

**Validation Rules**:

- `id_front`: required, file, mimes:jpeg,jpg,png,pdf, max:5120 (5MB)
- `id_back`: required, file, mimes:jpeg,jpg,png,pdf, max:5120 (5MB)
- `document_type`: nullable, in:passport,national_id,driver_license

**cURL Example**:

```bash
curl -X POST "http://localhost:8000/api/v1/verify" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -F "id_front=@/path/to/id_front.jpg" \
  -F "id_back=@/path/to/id_back.jpg" \
  -F "document_type=national_id"
```

**Response** (201 Created):

```json
{
  "message": "Verification documents submitted successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "status": "pending",
    "document_type": "national_id",
    "submitted_at": "2025-12-03T14:30:45.000000Z"
  }
}
```

**Error Responses**:

*400 Bad Request* - Duplicate submission

```json
{
  "message": "You already have a pending verification request."
}
```

*422 Unprocessable Entity* - Validation failed

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "id_front": [
      "The id front must be a file of type: jpeg, jpg, png, pdf."
    ],
    "id_back": [
      "The id back must not be greater than 5120 kilobytes."
    ]
  }
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

**Response** (200 OK):

```json
{
  "data": {
    "status": "verified",
    "document_type": "national_id",
    "submitted_at": "2025-12-03T14:30:45.000000Z",
    "reviewed_at": "2025-12-03T16:45:20.000000Z"
  }
}
```

**Possible Status Values**:

- `pending`: Documents are under review
- `verified`: User is verified
- `rejected`: Verification failed

**Response** (200 OK) - Rejected:

```json
{
  "data": {
    "status": "rejected",
    "document_type": "passport",
    "submitted_at": "2025-12-03T14:30:45.000000Z",
    "reviewed_at": "2025-12-03T16:45:20.000000Z",
    "admin_notes": "Documents are not clear. Please upload higher quality images."
  }
}
```

**Response** (404 Not Found) - No verification submitted:

```json
{
  "message": "No verification request found."
}
```

---

### Product Management Endpoints

#### Create Product

Create a new product listing. **Requires verified user**.

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
- `thumbnail` (file, required): Product thumbnail image (max 2MB)
- `images` (array, optional): Additional product images (max 5 images, each max 2MB)

**Validation Rules**:

- `category_id`: required, exists:categories,id
- `title`: required, string, max:255
- `description`: required, string
- `price_per_day`: required, numeric, min:1
- `is_for_sale`: boolean
- `sale_price`: required_if:is_for_sale,true, numeric, min:1
- `thumbnail`: required, image, mimes:jpeg,jpg,png,webp, max:2048
- `images`: array, max:5
- `images.*`: image, mimes:jpeg,jpg,png,webp, max:2048

**cURL Example**:

```bash
curl -X POST "http://localhost:8000/api/v1/products" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -F "category_id=2" \
  -F "title=Canon EOS R5 Camera" \
  -F "description=Professional mirrorless camera" \
  -F "price_per_day=50" \
  -F "is_for_sale=true" \
  -F "sale_price=2500" \
  -F "thumbnail=@/path/to/thumbnail.jpg" \
  -F "images[]=@/path/to/image1.jpg" \
  -F "images[]=@/path/to/image2.jpg"
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
    "thumbnail_url": "http://localhost:8000/storage/products/thumbnails/abc123.jpg",
    "image_urls": [
      "http://localhost:8000/storage/products/images/xyz789.jpg",
      "http://localhost:8000/storage/products/images/def456.jpg"
    ],
    "category": {
      "id": 2,
      "name": "Photography"
    },
    "created_at": "2025-12-03T14:30:45.000000Z"
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
    "thumbnail": [
      "The thumbnail must not be greater than 2048 kilobytes."
    ],
    "images": [
      "The images must not have more than 5 items."
    ]
  }
}
```

---

#### Update Product

Update an existing product. **Only product owner can update**.

**Endpoint**: `PUT /products/{id}` or `PATCH /products/{id}`

**Authentication**: Required (Product owner only)

**Headers**:

```http
Authorization: Bearer {token}
Content-Type: application/json
```

**Path Parameters**:

- `id` (integer, required): Product ID

**Request Body**:

```json
{
  "title": "Updated Camera Title",
  "price_per_day": 55.00,
  "is_available": true
}
```

**Validation Rules**:

- `category_id`: sometimes, exists:categories,id
- `title`: sometimes, string, max:255
- `description`: sometimes, string
- `price_per_day`: sometimes, numeric, min:1
- `is_for_sale`: boolean
- `sale_price`: required_if:is_for_sale,true, numeric, min:1
- `is_available`: boolean

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
    "thumbnail_url": "http://localhost:8000/storage/products/thumbnails/abc123.jpg",
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
  "message": "Resource not found."
}
```

---

#### Delete Product

Delete a product listing. **Only product owner can delete**.

**Endpoint**: `DELETE /products/{id}`

**Authentication**: Required (Product owner only)

**Headers**:

```http
Authorization: Bearer {token}
```

**Path Parameters**:

- `id` (integer, required): Product ID

**Response** (204 No Content):

```
No response body
```

**Note**: All product images (thumbnail and additional images) are automatically deleted from storage.

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
  "message": "Resource not found."
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
