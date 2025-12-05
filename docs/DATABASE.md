# Database Schema

## Overview

The Rented Marketplace API uses PostgreSQL as its primary database with 12 core models representing the complete rental and purchase marketplace ecosystem.

---

## Entity Relationship Diagram

```
┌─────────────┐       ┌──────────────┐       ┌─────────────────┐
│    users    │──────>│  products    │──────>│   categories    │
└─────────────┘       └──────────────┘       └─────────────────┘
      │                      │
      │                      │
      ├──────────────────────┼──────────────┐
      │                      │              │
      ▼                      ▼              ▼
┌─────────────┐       ┌──────────────┐ ┌─────────────┐
│   rentals   │       │  purchases   │ │   reviews   │
└─────────────┘       └──────────────┘ └─────────────┘
      │                      │              │
      │                      │              │
      └──────────┬───────────┘              │
                 │                          │
                 ▼                          ▼
          ┌──────────────┐         ┌─────────────┐
          │   disputes   │         │ favourites  │
          └──────────────┘         └─────────────┘

┌─────────────────┐       ┌──────────────────────┐
│ conversations   │──────>│     messages         │
└─────────────────┘       └──────────────────────┘
        │
        │
        ▼
  (user_one, user_two)

┌─────────────────────┐       ┌──────────────────────────┐
│ rental_availability │       │  user_verifications      │
└─────────────────────┘       └──────────────────────────┘
        │                              │
        │                              │
        ▼                              ▼
   (product_id)                   (user_id)
```

---

## Tables

### 1. `users`

User accounts and authentication data.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint | PK, auto_increment | Primary key |
| name | varchar(255) | NOT NULL | User's full name |
| email | varchar(255) | UNIQUE, NOT NULL | Email address |
| email_verified_at | timestamp | NULLABLE | Email verification time |
| password | varchar(255) | NOT NULL | Bcrypt hashed password |
| avatar | varchar(255) | NULLABLE | Avatar image path |
| verification_status | enum | DEFAULT 'unverified' | unverified, pending, verified, rejected |
| google_id | varchar(255) | NULLABLE, UNIQUE | Google OAuth ID |
| provider | varchar(255) | NULLABLE | OAuth provider name |
| google_token | text | NULLABLE | Google access token |
| google_refresh_token | text | NULLABLE | Google refresh token |
| remember_token | varchar(100) | NULLABLE | Session token |
| created_at | timestamp | NOT NULL | Creation timestamp |
| updated_at | timestamp | NOT NULL | Last update timestamp |

**Indexes:**
- Primary: `id`
- Unique: `email`, `google_id`

**Relationships:**
- Has many: `products`, `rentals`, `purchases`, `reviews`, `favourites`, `conversations`, `messages`, `disputes`
- Has one: `user_verification`

---

### 2. `categories`

Product categorization.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint | PK, auto_increment | Primary key |
| name | varchar(255) | NOT NULL | Category name |
| slug | varchar(255) | UNIQUE, NOT NULL | URL-friendly identifier |
| description | text | NULLABLE | Category description |
| created_at | timestamp | NOT NULL | Creation timestamp |
| updated_at | timestamp | NOT NULL | Last update timestamp |

**Indexes:**
- Primary: `id`
- Unique: `slug`

**Relationships:**
- Has many: `products`

---

### 3. `products`

Product listings for rent or sale.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint | PK, auto_increment | Primary key |
| user_id | bigint | FK, NOT NULL | Owner's user ID |
| category_id | bigint | FK, NOT NULL | Category ID |
| title | varchar(255) | NOT NULL | Product title |
| description | text | NOT NULL | Product description |
| price_per_day | decimal(10,2) | NOT NULL | Daily rental rate |
| price_per_week | decimal(10,2) | NULLABLE | Weekly rental rate |
| price_per_month | decimal(10,2) | NULLABLE | Monthly rental rate |
| is_for_sale | boolean | DEFAULT false | Available for purchase |
| sale_price | decimal(10,2) | NULLABLE | Purchase price |
| is_available | boolean | DEFAULT true | Currently available |
| verification_status | enum | DEFAULT 'pending' | pending, approved, rejected |
| rejection_reason | text | NULLABLE | Admin rejection reason |
| verified_at | timestamp | NULLABLE | Approval timestamp |
| thumbnail | varchar(255) | NULLABLE | Thumbnail image path |
| images | json | NULLABLE | Array of image paths |
| location_address | varchar(255) | NULLABLE | Street address |
| location_city | varchar(100) | NULLABLE | City |
| location_state | varchar(100) | NULLABLE | State/Province |
| location_country | varchar(100) | NULLABLE | Country |
| location_zip | varchar(20) | NULLABLE | Postal/ZIP code |
| location_latitude | decimal(10,8) | NULLABLE | GPS latitude |
| location_longitude | decimal(11,8) | NULLABLE | GPS longitude |
| delivery_available | boolean | DEFAULT false | Delivery offered |
| delivery_fee | decimal(8,2) | NULLABLE | Delivery charge |
| delivery_radius_km | integer | NULLABLE | Delivery distance (km) |
| pickup_available | boolean | DEFAULT true | Pickup allowed |
| product_condition | enum | NOT NULL | new, like_new, good, fair, worn |
| security_deposit | decimal(10,2) | NULLABLE | Required deposit |
| min_rental_days | integer | NULLABLE | Minimum rental period |
| max_rental_days | integer | NULLABLE | Maximum rental period |
| created_at | timestamp | NOT NULL | Creation timestamp |
| updated_at | timestamp | NOT NULL | Last update timestamp |

**Indexes:**
- Primary: `id`
- Foreign: `user_id` → users(id), `category_id` → categories(id)
- Index: `verification_status`, `is_available`, `location_city`

**Relationships:**
- Belongs to: `user`, `category`
- Has many: `rentals`, `purchases`, `reviews`, `favourites`, `rental_availability`, `conversations`, `disputes`

---

### 4. `user_verifications`

User identity verification documents.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint | PK, auto_increment | Primary key |
| user_id | bigint | FK, UNIQUE, NOT NULL | User ID |
| id_front | varchar(255) | NOT NULL | Front ID image path |
| id_back | varchar(255) | NOT NULL | Back ID image path |
| created_at | timestamp | NOT NULL | Creation timestamp |
| updated_at | timestamp | NOT NULL | Last update timestamp |

**Indexes:**
- Primary: `id`
- Unique: `user_id`
- Foreign: `user_id` → users(id)

**Relationships:**
- Belongs to: `user`

---

### 5. `rentals`

Rental booking transactions.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint | PK, auto_increment | Primary key |
| product_id | bigint | FK, NOT NULL | Product ID |
| user_id | bigint | FK, NOT NULL | Renter's user ID |
| start_date | date | NOT NULL | Rental start date |
| end_date | date | NOT NULL | Rental end date |
| total_price | decimal(10,2) | NOT NULL | Total rental cost |
| status | enum | DEFAULT 'pending' | pending, confirmed, completed, cancelled |
| delivery_required | boolean | DEFAULT false | Delivery requested |
| notes | text | NULLABLE | Additional notes |
| created_at | timestamp | NOT NULL | Creation timestamp |
| updated_at | timestamp | NOT NULL | Last update timestamp |

**Indexes:**
- Primary: `id`
- Foreign: `product_id` → products(id), `user_id` → users(id)
- Index: `status`, `start_date`, `end_date`

**Relationships:**
- Belongs to: `product`, `user`
- Has many: `rental_availability`, `disputes`

---

### 6. `purchases`

Purchase transactions.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint | PK, auto_increment | Primary key |
| product_id | bigint | FK, NOT NULL | Product ID |
| user_id | bigint | FK, NOT NULL | Buyer's user ID |
| purchase_price | decimal(10,2) | NOT NULL | Final purchase price |
| status | enum | DEFAULT 'pending' | pending, completed, cancelled |
| delivery_required | boolean | DEFAULT false | Delivery requested |
| notes | text | NULLABLE | Additional notes |
| created_at | timestamp | NOT NULL | Creation timestamp |
| updated_at | timestamp | NOT NULL | Last update timestamp |

**Indexes:**
- Primary: `id`
- Foreign: `product_id` → products(id), `user_id` → users(id)
- Index: `status`

**Relationships:**
- Belongs to: `product`, `user`
- Has many: `disputes`

---

### 7. `reviews`

Product reviews and ratings.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint | PK, auto_increment | Primary key |
| user_id | bigint | FK, NOT NULL | Reviewer's user ID |
| product_id | bigint | FK, NOT NULL | Product ID |
| rating | integer | NOT NULL, CHECK(1-5) | Star rating (1-5) |
| comment | text | NULLABLE | Review text |
| created_at | timestamp | NOT NULL | Creation timestamp |
| updated_at | timestamp | NOT NULL | Last update timestamp |

**Indexes:**
- Primary: `id`
- Foreign: `user_id` → users(id), `product_id` → products(id)
- Unique: (`user_id`, `product_id`)

**Relationships:**
- Belongs to: `user`, `product`

---

### 8. `favourites`

User wishlist/favourites.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint | PK, auto_increment | Primary key |
| user_id | bigint | FK, NOT NULL | User ID |
| product_id | bigint | FK, NOT NULL | Product ID |
| created_at | timestamp | NOT NULL | Creation timestamp |
| updated_at | timestamp | NOT NULL | Last update timestamp |

**Indexes:**
- Primary: `id`
- Foreign: `user_id` → users(id), `product_id` → products(id)
- Unique: (`user_id`, `product_id`)

**Relationships:**
- Belongs to: `user`, `product`

---

### 9. `conversations`

User-to-user message threads.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint | PK, auto_increment | Primary key |
| user_one_id | bigint | FK, NOT NULL | First participant |
| user_two_id | bigint | FK, NOT NULL | Second participant |
| product_id | bigint | FK, NOT NULL | Related product |
| last_message_at | timestamp | NULLABLE | Last message time |
| created_at | timestamp | NOT NULL | Creation timestamp |
| updated_at | timestamp | NOT NULL | Last update timestamp |

**Indexes:**
- Primary: `id`
- Foreign: `user_one_id` → users(id), `user_two_id` → users(id), `product_id` → products(id)
- Index: `last_message_at`

**Relationships:**
- Belongs to: `user_one`, `user_two`, `product`
- Has many: `messages`

---

### 10. `messages`

Individual messages within conversations.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint | PK, auto_increment | Primary key |
| conversation_id | bigint | FK, NOT NULL | Conversation ID |
| sender_id | bigint | FK, NOT NULL | Sender's user ID |
| content | text | NOT NULL | Message text |
| is_read | boolean | DEFAULT false | Read status |
| read_at | timestamp | NULLABLE | Read timestamp |
| created_at | timestamp | NOT NULL | Creation timestamp |
| updated_at | timestamp | NOT NULL | Last update timestamp |

**Indexes:**
- Primary: `id`
- Foreign: `conversation_id` → conversations(id), `sender_id` → users(id)
- Index: `is_read`

**Relationships:**
- Belongs to: `conversation`, `sender` (user)

---

### 11. `rental_availability`

Product availability calendar and date blocking.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint | PK, auto_increment | Primary key |
| product_id | bigint | FK, NOT NULL | Product ID |
| blocked_date | date | NOT NULL | Blocked date |
| block_type | enum | NOT NULL | booked, maintenance |
| rental_id | bigint | FK, NULLABLE | Associated rental ID |
| notes | text | NULLABLE | Blocking reason |
| created_at | timestamp | NOT NULL | Creation timestamp |
| updated_at | timestamp | NOT NULL | Last update timestamp |

**Indexes:**
- Primary: `id`
- Foreign: `product_id` → products(id), `rental_id` → rentals(id)
- Unique: (`product_id`, `blocked_date`)
- Index: `blocked_date`

**Relationships:**
- Belongs to: `product`, `rental` (optional)

---

### 12. `disputes`

Issue reporting and resolution.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint | PK, auto_increment | Primary key |
| rental_id | bigint | FK, NULLABLE | Related rental |
| purchase_id | bigint | FK, NULLABLE | Related purchase |
| reported_by | bigint | FK, NOT NULL | Reporter's user ID |
| reported_against | bigint | FK, NOT NULL | Reported user ID |
| dispute_type | enum | NOT NULL | damage, late_return, not_as_described, payment, other |
| status | enum | DEFAULT 'open' | open, investigating, resolved, closed |
| description | text | NOT NULL | Dispute details |
| evidence | json | NULLABLE | Evidence URLs array |
| resolution | text | NULLABLE | Resolution details |
| created_at | timestamp | NOT NULL | Creation timestamp |
| updated_at | timestamp | NOT NULL | Last update timestamp |

**Indexes:**
- Primary: `id`
- Foreign: `rental_id` → rentals(id), `purchase_id` → purchases(id), `reported_by` → users(id), `reported_against` → users(id)
- Index: `status`, `dispute_type`

**Relationships:**
- Belongs to: `rental` (optional), `purchase` (optional), `reporter` (user), `reported_user` (user)

---

## Migrations

### Migration Order

1. `create_users_table`
2. `create_cache_table`
3. `create_jobs_table`
4. `create_categories_table`
5. `create_products_table`
6. `create_personal_access_tokens_table`
7. `create_user_verifications_table`
8. `create_rentals_table`
9. `create_purchases_table`
10. `create_reviews_table`
11. `create_favourites_table`
12. `create_conversations_table`
13. `create_messages_table`
14. `add_google_oauth_fields_to_users_table`
15. `add_location_and_delivery_to_products_table`
16. `create_rental_availability_table`
17. `create_disputes_table`
18. `add_verification_status_to_products_table`
19. `add_avatar_to_users_table`

### Running Migrations

```bash
# Run all pending migrations
php artisan migrate

# Run migrations with seeding
php artisan migrate --seed

# Rollback last batch
php artisan migrate:rollback

# Check migration status
php artisan migrate:status

# Fresh migration (drops all tables)
php artisan migrate:fresh --seed
```

---

## Seeders

### Available Seeders

- `DatabaseSeeder` - Master seeder
- `CategorySeeder` - Product categories
- `UserSeeder` - Sample users (10 users)
- `ProductSeeder` - Sample products (50 products)

### Seeding Database

```bash
# Run all seeders
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=CategorySeeder

# Fresh migration with seeding
php artisan migrate:fresh --seed
```

**See [SEEDING_GUIDE.md](../SEEDING_GUIDE.md) for detailed instructions.**

---

## Data Types

### Enums

**User Verification Status:**
- `unverified` - Default, no documents uploaded
- `pending` - Documents submitted, awaiting review
- `verified` - Approved by admin
- `rejected` - Documents rejected

**Product Verification Status:**
- `pending` - Awaiting admin approval
- `approved` - Approved for listing
- `rejected` - Rejected by admin

**Product Condition:**
- `new` - Brand new, unused
- `like_new` - Excellent condition, minimal use
- `good` - Good condition, normal wear
- `fair` - Fair condition, visible wear
- `worn` - Well-used, functional

**Rental Status:**
- `pending` - Awaiting owner confirmation
- `confirmed` - Confirmed by owner
- `completed` - Rental period ended
- `cancelled` - Cancelled by user/owner

**Purchase Status:**
- `pending` - Awaiting payment/confirmation
- `completed` - Purchase completed
- `cancelled` - Purchase cancelled

**Dispute Type:**
- `damage` - Product damaged
- `late_return` - Return overdue
- `not_as_described` - Product mismatch
- `payment` - Payment issue
- `other` - Other issues

**Dispute Status:**
- `open` - Newly reported
- `investigating` - Under review
- `resolved` - Issue resolved
- `closed` - Dispute closed

**Block Type:**
- `booked` - Blocked by rental
- `maintenance` - Blocked for maintenance

---

## Foreign Key Constraints

All foreign keys use `CASCADE` on update and `RESTRICT` on delete to maintain referential integrity.

**Example:**
```sql
FOREIGN KEY (user_id) REFERENCES users(id)
  ON UPDATE CASCADE
  ON DELETE RESTRICT
```

---

## Indexes

### Performance Indexes

- `users.email` - Login queries
- `users.google_id` - OAuth lookups
- `products.verification_status` - Admin filtering
- `products.is_available` - Product browsing
- `products.location_city` - Location searches
- `rentals.status` - Status filtering
- `rentals.start_date`, `rentals.end_date` - Date range queries
- `messages.is_read` - Unread message count
- `rental_availability.blocked_date` - Availability checks

---

## Best Practices

### Query Optimization

- Use eager loading to prevent N+1 queries
- Index foreign keys and frequently queried columns
- Use `select()` to limit returned columns
- Implement pagination for large datasets

### Data Integrity

- Use transactions for related operations
- Validate data in Form Requests before database insertion
- Use Eloquent relationships for data access
- Implement soft deletes for audit trails (if needed)

### Security

- Never expose raw IDs in URLs (consider UUIDs)
- Use query builder/Eloquent to prevent SQL injection
- Validate all user input before database operations
- Use database-level constraints as last line of defense

---

## Database Configuration

### Development (SQLite)

```env
DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite
```

### Production (PostgreSQL)

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=rented_marketplace
DB_USERNAME=postgres
DB_PASSWORD=secret
```

---

## Backup & Recovery

### Backup Commands

```bash
# PostgreSQL backup
pg_dump -U postgres rented_marketplace > backup.sql

# Restore from backup
psql -U postgres rented_marketplace < backup.sql
```

### Laravel Database Commands

```bash
# Export schema
php artisan schema:dump

# Fresh install with backup
php artisan migrate:fresh --seed
```

---

**Last Updated**: December 5, 2025
