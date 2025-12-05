# Rented Marketplace API

> A comprehensive RESTful API for a rental and purchase marketplace platform built with Laravel 12, featuring real-time messaging, product verification, rental availability management, and dispute resolution.

## 🚀 Quick Start

### Prerequisites

- PHP 8.4+
- PostgreSQL 16
- Redis 7
- Composer
- Docker (optional)

### Installation

```bash
# Clone the repository
git clone https://github.com/abdulbarry-dev/rented-api.git
cd rented-api

# Install dependencies
composer install

# Configure environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed

# Start server
php artisan octane:start
```

**Docker Setup**: See [DOCKER_DEPLOYMENT.md](./DOCKER_DEPLOYMENT.md)

---

## 📚 Documentation

### API Documentation

- **[Authentication](./docs/api/AUTHENTICATION.md)** - Register, login, logout, OAuth, password reset
- **[User Profile](./docs/api/USER_PROFILE.md)** - Profile management, avatar, verification
- **[Categories](./docs/api/CATEGORIES.md)** - Product categories
- **[Products](./docs/api/PRODUCTS.md)** - Product listings, CRUD operations
- **[Reviews](./docs/api/REVIEWS.md)** - Product reviews and ratings
- **[Favourites](./docs/api/FAVOURITES.md)** - Wishlist management
- **[Conversations](./docs/api/CONVERSATIONS.md)** - Real-time messaging
- **[Rental Availability](./docs/api/RENTAL_AVAILABILITY.md)** - Calendar and date blocking
- **[Rentals](./docs/api/RENTALS.md)** - Rental transactions
- **[Purchases](./docs/api/PURCHASES.md)** - Purchase transactions
- **[Disputes](./docs/api/DISPUTES.md)** - Issue reporting and resolution
- **[Product Verification](./docs/api/PRODUCT_VERIFICATION.md)** - Admin approval system

### Technical Documentation

- **[Database Schema](./docs/DATABASE.md)** - Complete database structure
- **[Seeding Guide](./SEEDING_GUIDE.md)** - Database seeding instructions
- **[Storage Setup](./STORAGE_SETUP.md)** - File storage configuration

---

## 🌐 Base URLs

| Environment | URL |
|------------|-----|
| Development | `http://localhost:8000/api/v1` |
| Production | `https://api.rentedmarketplace.com/api/v1` |
| Docker | `http://localhost:8000/api/v1` |

---

## 🔑 Authentication

The API uses **Laravel Sanctum** for token-based authentication.

### Quick Example

```bash
# Register
curl -X POST http://localhost:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Response includes token
{
  "message": "User registered successfully",
  "token": "1|AbCdEfGhIjKlMnOp...",
  "user": { ... }
}

# Use token in subsequent requests
curl -X GET http://localhost:8000/api/v1/user \
  -H "Authorization: Bearer 1|AbCdEfGhIjKlMnOp..." \
  -H "Accept: application/json"
```

**See [Authentication Docs](./docs/api/AUTHENTICATION.md) for complete details.**

---

## 🎯 Key Features

### ✅ Core Features

- **Authentication** - Token-based (Sanctum) + Google OAuth
- **Product Management** - CRUD with image uploads, categories, search
- **Product Verification** - Admin approval workflow (pending/approved/rejected)
- **Reviews & Ratings** - User reviews with 1-5 star ratings
- **Favourites** - Wishlist/bookmark products
- **Rental System** - Date-based rentals with availability calendar
- **Purchase System** - Direct purchase transactions
- **Messaging** - Real-time conversations between users
- **Disputes** - Issue reporting with evidence and resolution
- **User Verification** - ID document upload for verified accounts
- **Avatar Upload** - Profile picture management

### 🏗️ Architecture

- **Framework**: Laravel 12
- **Performance**: Laravel Octane with Swoole
- **Database**: PostgreSQL with optimized indexes
- **Caching**: Redis for session and query caching
- **File Storage**: Local/S3-compatible storage
- **API Pattern**: Repository → Service → Controller
- **Validation**: Form Request classes
- **Authorization**: Policy-based access control
- **Code Style**: PSR-12 via Laravel Pint

---

## 📋 API Endpoints Overview

### Health Check

```
GET /api/health
```

### Authentication (9 endpoints)

- `POST /register` - Register new user
- `POST /login` - Authenticate user
- `POST /logout` - Revoke token
- `GET /user` - Get current user
- `GET /auth/google` - OAuth redirect
- `GET /auth/google/callback` - OAuth callback
- `POST /forgot-password` - Request reset
- `POST /reset-password` - Reset password

### Products (15+ endpoints)

- `GET /products` - Browse products (with filters)
- `POST /products` - Create product
- `GET /products/{id}` - Get product details
- `PUT /products/{id}` - Update product
- `DELETE /products/{id}` - Delete product
- `GET /products/{id}/reviews` - Get product reviews
- `GET /products/{id}/availability` - Check rental dates
- _...and more_

### Rentals & Purchases (12 endpoints)

- Rental creation, status updates, history
- Purchase creation, completion, cancellation
- User's rental/purchase history

### Communication (8 endpoints)

- Conversations, messages, unread count
- Real-time messaging between users

### Admin (5 endpoints)

- Product verification (approve/reject)
- Pending, approved, rejected lists

**See individual API docs for complete endpoint details.**

---

## 📊 Response Format

### Success Response

```json
{
  "message": "Operation successful",
  "data": {
    "id": 1,
    "name": "Product Name",
    "created_at": "2025-12-03T14:30:00.000000Z"
  }
}
```

### Error Response

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

### Pagination

```json
{
  "data": [ ... ],
  "links": {
    "first": "http://api.example.com/products?page=1",
    "last": "http://api.example.com/products?page=10",
    "prev": null,
    "next": "http://api.example.com/products?page=2"
  },
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 150
  }
}
```

---

## 🔒 Security

### Authentication

- **Sanctum Tokens** - Bearer token authentication
- **Token Revocation** - Logout revokes current token
- **Password Hashing** - Bcrypt with cost factor 10
- **CSRF Protection** - For web routes
- **Rate Limiting** - 60 req/min public, 120 req/min authenticated

### Authorization

- **Policy-Based** - ReviewPolicy, ProductPolicy, etc.
- **Owner Checks** - Users can only modify their own resources
- **Admin Gates** - Product verification restricted to admins

### Input Validation

- **Form Requests** - All input validated via dedicated classes
- **Type Safety** - Strict type declarations in PHP 8.4
- **SQL Injection** - Protected via Eloquent ORM
- **XSS Protection** - Output escaping in responses

---

## 📈 Performance

- **Laravel Octane** - Swoole for high-performance request handling
- **Redis Caching** - Query and session caching
- **Eager Loading** - N+1 query prevention
- **Database Indexes** - Optimized queries on foreign keys
- **Image Optimization** - WebP format support

---

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/ProductTest.php

# Run with coverage
php artisan test --coverage
```

---

## 📝 Code Quality

```bash
# Format code (PSR-12)
./vendor/bin/pint

# Check formatting
./vendor/bin/pint --test

# Run static analysis
./vendor/bin/phpstan analyse
```

---

## 🗄️ Database

### Models

- **User** - Users, authentication, profiles
- **Category** - Product categories
- **Product** - Product listings (rent/sale)
- **Rental** - Rental transactions
- **Purchase** - Purchase transactions
- **Review** - Product reviews
- **Favourite** - User wishlist
- **Conversation** - Message threads
- **Message** - Individual messages
- **RentalAvailability** - Calendar blocking
- **Dispute** - Issue reporting
- **UserVerification** - ID verification

**See [Database Schema](./docs/DATABASE.md) for complete details.**

---

## 🚀 Deployment

### Production Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure PostgreSQL connection
- [ ] Set up Redis for caching
- [ ] Configure file storage (S3)
- [ ] Set up SSL certificate
- [ ] Configure CORS settings
- [ ] Set rate limiting rules
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Cache config: `php artisan config:cache`
- [ ] Cache routes: `php artisan route:cache`
- [ ] Start Octane: `php artisan octane:start --server=swoole`

**See [Docker Deployment Guide](./DOCKER_DEPLOYMENT.md)**

---

## 📖 Additional Resources

### Guides

- [Postman Testing](./POSTMAN_TESTING_GUIDE.md)
- [Docker Implementation](./DOCKER_IMPLEMENTATION_GUIDE.md)
- [Avatar Upload Feature](./AVATAR_UPLOAD_FEATURE.md)
- [Product Image Upload](./PRODUCT_IMAGE_UPLOAD_FEATURE.md)
- [Test Results](./TEST_RESULTS.md)

### External Links

- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Sanctum](https://laravel.com/docs/sanctum)
- [Laravel Octane](https://laravel.com/docs/octane)

---

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

### Code Standards

- Follow PSR-12 coding standards
- Run `./vendor/bin/pint` before committing
- Write tests for new features
- Update documentation

---

## 📄 License

This project is proprietary software. All rights reserved.

---

## 📞 Support

- **Issues**: Open GitHub issue
- **Email**: support@rentedmarketplace.com
- **Documentation**: `/docs` directory

---

## 🏷️ Version

- **API Version**: v1
- **Laravel**: 12.x
- **PHP**: 8.4+
- **Last Updated**: December 5, 2025

---

**Built with ❤️ using Laravel 12, PostgreSQL, Redis, and Laravel Octane**
