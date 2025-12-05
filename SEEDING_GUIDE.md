# Database Seeding Guide for Rented API

This guide explains how to populate your database with realistic test data for production testing.

---

## Overview

The seeding system creates:
- **21 categories** (Facebook Marketplace-style: Electronics, Vehicles, Home & Garden, Sports, Events, etc.)
- **10 verified users** with laptop avatars
- **33+ realistic products** across all categories with detailed descriptions

All test data uses a single laptop image to test image upload functionality consistently.

---

## Quick Start

### Step 1: Prepare Your Test Image

1. Place your laptop image (or any test image) at:
   ```
   storage/app/seed-images/laptop.jpg
   ```

2. Supported formats: `.jpg`, `.jpeg`, `.png`, `.webp`

### Step 2: Run Image Preparation

```bash
php artisan db:seed --class=PrepareTestImages
```

This copies your test image to:
- `storage/app/public/avatars/laptop_test.jpg` (for user avatars)
- `storage/app/public/products/images/laptop_test.jpg` (for product images)

### Step 3: Seed Database

**Fresh installation:**
```bash
php artisan migrate:fresh --seed
```

**Re-seed only (keeps migrations):**
```bash
php artisan db:seed
```

**Seed specific seeder:**
```bash
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=ProductSeeder
```

---

## Docker Usage

### Local Development

```bash
# 1. Place test image
mkdir -p storage/app/seed-images
cp ~/Downloads/laptop.jpg storage/app/seed-images/laptop.jpg

# 2. Prepare images
docker compose exec app php artisan db:seed --class=PrepareTestImages

# 3. Seed database
docker compose exec app php artisan migrate:fresh --seed
```

### VPS Production Testing

```bash
# 1. SSH into VPS
ssh user@167.86.87.72

# 2. Navigate to project
cd /path/to/rented-api

# 3. Place test image
mkdir -p storage/app/seed-images
scp laptop.jpg user@167.86.87.72:/path/to/rented-api/storage/app/seed-images/

# 4. Run seeders in Docker
sudo docker compose exec app php artisan db:seed --class=PrepareTestImages
sudo docker compose exec app php artisan migrate:fresh --seed

# 5. Test API
curl http://167.86.87.72:8000/api/v1/products
curl -I http://167.86.87.72:8000/storage/avatars/laptop_test.jpg
curl -I http://167.86.87.72:8000/storage/products/images/laptop_test.jpg
```

---

## Seeder Details

### CategorySeeder
Creates 21 marketplace categories:

**Electronics & Tech:**
- Electronics, Cameras & Photography, Audio & Music, Gaming

**Vehicles:**
- Vehicles, Bikes

**Home & Garden:**
- Home & Garden, Tools & Equipment, Appliances

**Sports & Outdoors:**
- Sports Equipment, Camping & Outdoor, Water Sports

**Events:**
- Party Supplies, Event Equipment

**Fashion:**
- Clothing & Accessories, Jewelry & Watches

**Others:**
- Baby & Kids, Books & Media, Games & Toys, Business & Industrial, Medical Equipment

### UserSeeder
Creates 10 verified users:
- Test user: `test@example.com` / `password`
- 9 additional users with random names
- All have `laptop_test.jpg` as avatar
- All have `verification_status = 'verified'`

### ProductSeeder
Creates 33+ realistic products:

**Sample Products:**
- MacBook Pro 16" M3 Max ($75/day)
- Canon EOS R5 Camera ($120/day)
- Tesla Model 3 Long Range ($250/day)
- BMW X5 SUV ($180/day)
- Trek Mountain Bike ($35/day)
- Party Tent 20x20ft ($150/day)
- DJ Sound System ($120/day)
- PlayStation 5 Bundle ($40/day)
- And many more...

**Product Properties:**
- Realistic titles and descriptions
- Varied pricing ($30-$250/day)
- 50% chance of being for sale
- Sale price = rental price × 20-30
- 3 images per product (all laptop_test.jpg)
- Random user assignment
- Category-appropriate descriptions

---

## Testing Workflow

### 1. Verify Database Content

```bash
# Check categories
php artisan tinker
>>> \App\Models\Category::count(); // Should be 21
>>> \App\Models\Category::pluck('name');

# Check users
>>> \App\Models\User::count(); // Should be 10
>>> \App\Models\User::first()->avatar_path;

# Check products
>>> \App\Models\Product::count(); // Should be 33+
>>> \App\Models\Product::with('category', 'user')->first();
```

### 2. Test API Endpoints

```bash
# Get all products
curl http://localhost:8000/api/v1/products

# Get single product
curl http://localhost:8000/api/v1/products/1

# Get categories
curl http://localhost:8000/api/v1/categories

# Test authentication
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'
```

### 3. Test Image Access

```bash
# User avatar (public)
curl -I http://localhost:8000/storage/avatars/laptop_test.jpg

# Product images (public)
curl -I http://localhost:8000/storage/products/images/laptop_test.jpg

# Should return: HTTP/1.1 200 OK
# Content-Type: image/jpeg
# Cache-Control: public, max-age=31536000
```

---

## Troubleshooting

### Images Not Found (404)

**Issue:** `http://localhost:8000/storage/avatars/laptop_test.jpg` returns 404

**Solution:**
```bash
# Ensure storage link exists
php artisan storage:link

# In Docker
docker compose exec app php artisan storage:link
```

### Permission Errors

**Issue:** Cannot write to storage directories

**Solution:**
```bash
# Fix permissions
chmod -R 775 storage bootstrap/cache public
chown -R www-data:www-data storage bootstrap/cache public

# In Docker (already handled by Dockerfile)
docker compose exec app chmod -R 775 storage
```

### Seeders Not Running

**Issue:** Seeders run but no data appears

**Solution:**
```bash
# Check DatabaseSeeder.php calls your seeders
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=ProductSeeder
```

### Images Don't Copy

**Issue:** PrepareTestImages says "Source image not found"

**Solution:**
```bash
# Check image exists
ls -lh storage/app/seed-images/laptop.jpg

# Create directory if missing
mkdir -p storage/app/seed-images

# Copy your image
cp ~/Downloads/your-image.jpg storage/app/seed-images/laptop.jpg
```

---

## Production Deployment Checklist

Before deploying to VPS:

- [ ] Test seeders locally: `php artisan migrate:fresh --seed`
- [ ] Verify all images load: Check /storage/avatars and /storage/products/images
- [ ] Test API returns products with images
- [ ] Test user authentication with test@example.com
- [ ] Verify upload limits (20MB) work for new products
- [ ] Check Nginx serves static files correctly
- [ ] Test image optimization service compresses properly

On VPS:

- [ ] Pull latest code with seeder updates
- [ ] Copy test image to `storage/app/seed-images/`
- [ ] Run `PrepareTestImages` seeder
- [ ] Run `migrate:fresh --seed`
- [ ] Test public API endpoints
- [ ] Test image URLs return 200 OK
- [ ] Test authenticated product creation
- [ ] Monitor logs for errors: `docker compose logs -f app`

---

## Customization

### Add More Products

Edit `database/seeders/ProductSeeder.php` and add to the `$products` array:

```php
[
    'title' => 'Your Product Name',
    'description' => 'Detailed description',
    'price' => 50.00,
    'category' => 'Electronics' // Must match CategorySeeder name
],
```

### Add More Categories

Edit `database/seeders/CategorySeeder.php`:

```php
['name' => 'New Category', 'slug' => 'new-category', 'description' => 'Description'],
```

### Use Different Images

Replace `laptop_test.jpg` paths in seeders with your own:

```php
// UserSeeder.php
$avatar = 'avatars/your_image.jpg';

// ProductSeeder.php
$testImage = 'products/images/your_image.jpg';
```

---

## Next Steps

1. **Run seeders** to populate test data
2. **Test API** with Flutter mobile app
3. **Upload real products** through API
4. **Test verification flow** with document uploads
5. **Monitor performance** with realistic data volumes

For more information:
- [README.md](README.md) - Full API documentation
- [FLUTTER_INTEGRATION_GUIDE.md](FLUTTER_INTEGRATION_GUIDE.md) - Mobile app integration
- [DOCKER_DEPLOYMENT.md](DOCKER_DEPLOYMENT.md) - VPS deployment guide
