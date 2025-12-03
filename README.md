# Rented Marketplace API Documentation

## 1. Project Overview

Rented is a full-featured marketplace application where users can **rent** or **sell** items. The platform allows public browsing of listings, while only authenticated and verified users can manage products or participate in renting and buying flows.

The backend is built using **Laravel**, secured with **Laravel Sanctum**, and optimized with packages like **Laravel Octane** for improved performance.

---

## 2. Development Phases Overview

1. **Phase 1 — Public API (No Auth Required)**
2. **Phase 2 — Authentication & Sanctum Setup**
3. **Phase 3 — User Verification (International ID Upload)**
4. **Phase 4 — Product Management (CRUD)**
5. **Phase 5 — Renting & Buying Flow**
6. **Phase 6 — User Profile Management**
7. **Phase 7 — Performance Optimization (Octane, Caching, etc.)**

---

# Phase 1 — Public API

## Description

Public users can view product listings, categories, and general marketplace information without authentication.

## Endpoints

### GET /api/products

**Description:** Fetch all products.

#### Example Response

```json
{
  "data": [
    {
      "id": 1,
      "title": "Camera DSLR",
      "price_per_day": 20,
      "is_available": true,
      "thumbnail": "https://cdn.rented.com/img/cam1.jpg"
    }
  ]
}
```

### GET /api/products/{id}

**Description:** Fetch single product details.

### GET /api/categories

**Description:** Fetch available product categories.

## Notes

* No authentication required.
* Only read-only endpoints.

---

# Phase 2 — Authentication (Laravel Sanctum)

## Description

Users must register and log in before performing any privileged actions. Sanctum will be used for API authentication.

## Sanctum Setup

### Installation

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\\Sanctum\\SanctumServiceProvider"
php artisan migrate
```

### Middleware

Enable Sanctum in `app/Http/Kernel.php`:

```php
'api' => [
  \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
  'throttle:api',
  \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

---

## Endpoints

### POST /api/register

Registers a new user.

#### Request

```json
{
  "name": "Abdul Barry",
  "email": "abdul@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

#### Validation

* name: required, string
* email: required, email, unique
* password: required, confirmed, min:8

### POST /api/login

Logs in a user and issues a Sanctum token.

#### Response

```json
{
  "token": "1|jskhfhkshf98shf9s8dfh9sd"
}
```

### POST /api/logout

Revokes the user token.

## Notes

* Tokens must be sent via `Authorization: Bearer {token}`.

---

# Phase 3 — User Verification

## Description

A user must upload an **international ID** (passport/NID/driver’s license) before they can create listings.

## Endpoints

### POST /api/verify

Uploads verification documents.

#### Request

`multipart/form-data`

```json
{
  "id_front": "file",
  "id_back": "file"
}
```

#### Validation

* id_front: required, image
* id_back: required, image

### GET /api/verify/status

Returns verification status.

#### Response

```json
{
  "status": "pending" | "verified" | "rejected"
}
```

## Notes

* Only verified users can manage products.

---

# Phase 4 — Product Management (CRUD)

## Description

Verified users can create, update, and delete product listings.

## Endpoints

### POST /api/products

**Requires verification**

#### Request

```json
{
  "title": "MacBook Pro 2021",
  "description": "Great condition laptop",
  "price_per_day": 40,
  "is_for_sale": true,
  "sale_price": 1200,
  "images": ["file", "file"]
}
```

#### Validation

* title: required
* description: required
* price_per_day: required, numeric
* images: array
* is_for_sale: boolean

---

### PUT /api/products/{id}

### DELETE /api/products/{id}

### GET /api/user/products

List user-owned products.

## Notes

* All product endpoints use `auth:sanctum` + `verified` middleware.

---

# Phase 5 — Renting & Buying Flow

## Description

Verified users can rent or buy items.

## Endpoints

### POST /api/rent/{productId}

#### Request

```json
{
  "start_date": "2025-08-01",
  "end_date": "2025-08-05"
}
```

#### Validation

* start_date: required, date
* end_date: required, date, after:start_date

### POST /api/buy/{productId}

Purchases an item.

---

# Phase 6 — User Profile Management

### GET /api/user

Get user profile.

### PUT /api/user

Update profile.

#### Request

```json
{
  "username": "abdul.dev",
  "full_name": "Abdul Barry",
  "avatar": "file"
}
```

#### Validation

* username: string, unique
* full_name: string
* avatar: image

---

# Phase 7 — Performance Optimization

## Laravel Octane

### Installation

```bash
composer require laravel/octane
php artisan octane:install
```

### Running Octane

```bash
php artisan octane:start --server=swoole
```

### Notes

* Improves request throughput.
* Ideal for high-traffic marketplace.
* Combine with Redis caching.

---

# Final Notes

* All endpoints follow RESTful conventions.
* This documentation is structured to feed into AI development agents.
* Expand phases as the app evolves (payments, reviews, messaging, etc.).
