# Seeding Categories and Products on VPS Without Affecting Users

This guide shows how to add new categories and products to your production VPS without running `migrate:fresh` which would delete all existing data.

---

## ⚠️ Important

**DO NOT run `php artisan migrate:fresh --seed` on production!**

This will delete all users, products, rentals, purchases, conversations, and other data.

---

## Safe Approach: Run Seeders Only

### Step 1: SSH into VPS

```bash
ssh user@167.86.87.72
cd /path/to/rented-api
```

### Step 2: Pull Latest Changes

```bash
git pull origin main
```

### Step 3: Prepare Test Image (Optional)

If you want to use the laptop test image for new seeded products:

```bash
# Create seed-images directory
mkdir -p storage/app/seed-images

# Upload your image (from local machine)
scp laptop.jpg user@167.86.87.72:/path/to/rented-api/storage/app/seed-images/

# Or create a placeholder
sudo docker compose exec app php scripts/create-test-image.php
```

### Step 4: Prepare Images

```bash
sudo docker compose exec app php artisan db:seed --class=PrepareTestImages
```

**Output:**

```
✅ Copied laptop image to: storage/app/public/avatars/laptop_test.jpg
✅ Copied laptop image to: storage/app/public/products/images/laptop_test.jpg
🎉 Test images prepared successfully!
```

### Step 5: Seed Categories (Safe - Skips Existing)

```bash
sudo docker compose exec app php artisan db:seed --class=CategorySeeder
```

This will add **21 new categories** like:

- Electronics, Cameras & Photography, Audio & Music, Gaming
- Vehicles, Bikes
- Home & Garden, Tools & Equipment, Appliances
- Sports Equipment, Camping & Outdoor, Water Sports
- Party Supplies, Event Equipment
- Clothing & Accessories, Jewelry & Watches
- Baby & Kids, Books & Media, Games & Toys
- Business & Industrial, Medical Equipment

**Note:** If categories already exist with the same name, Laravel will throw a unique constraint error and skip them. This is safe.

### Step 6: Seed Products (Safe - Adds New Products)

```bash
sudo docker compose exec app php artisan db:seed --class=ProductSeeder
```

This will add **34 new realistic products** across all categories without affecting existing products or users.

Products include:

- MacBook Pro 16" M3 Max ($75/day)
- Canon EOS R5 Camera ($120/day)
- Tesla Model 3 Long Range ($250/day)
- BMW X5 SUV ($180/day)
- And 30+ more...

### Step 7: Verify New Data

```bash
# Check categories count
curl http://167.86.87.72:8000/api/v1/categories | jq '.data | length'

# Check products count
curl http://167.86.87.72:8000/api/v1/products | jq '.meta.total'

# Check users are still there
sudo docker compose exec app php artisan tinker --execute="echo 'Users: ' . \App\Models\User::count() . PHP_EOL;"
```

---

## Alternative: Custom Seeder for Production

If you want more control, create a production-safe seeder:

### Create ProductionSeeder.php

```bash
sudo docker compose exec app php artisan make:seeder ProductionSeeder
```

**File: `database/seeders/ProductionSeeder.php`**

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Seeding production data (safe mode)...');
        
        // Only seed if not already seeded
        $categoriesCount = \App\Models\Category::count();
        
        if ($categoriesCount < 21) {
            $this->command->info('📦 Adding categories...');
            $this->call(CategorySeeder::class);
        } else {
            $this->command->info('✅ Categories already seeded, skipping...');
        }
        
        // Always add products (won't affect existing ones)
        $this->command->info('📦 Adding test products...');
        $this->call(ProductSeeder::class);
        
        $this->command->info('✅ Production seeding completed!');
    }
}
```

### Run Production Seeder

```bash
sudo docker compose exec app php artisan db:seed --class=ProductionSeeder
```

---

## What Gets Preserved

✅ **All existing data remains intact:**

- Users and authentication tokens
- Products created by real users
- Rentals and purchases
- Reviews and ratings
- Conversations and messages
- Favourites
- Disputes
- User verifications

❌ **What gets added:**

- 21 marketplace categories (if not exist)
- 34 test products with realistic data
- Test images for avatars and products

---

## Rollback (If Needed)

If you accidentally added test products and want to remove them:

```bash
# Delete products created by a specific test user
sudo docker compose exec app php artisan tinker --execute="
\App\Models\Product::where('user_id', 1)->delete();
echo 'Deleted test products';
"

# Or delete products with laptop_test.jpg images
sudo docker compose exec app php artisan tinker --execute="
\App\Models\Product::whereJsonContains('images', 'products/images/laptop_test.jpg')->delete();
echo 'Deleted products with test images';
"
```

---

## Best Practice for Production

**Development/Staging:**

```bash
php artisan migrate:fresh --seed  # Safe to wipe everything
```

**Production:**

```bash
php artisan migrate                              # Run new migrations only
php artisan db:seed --class=CategorySeeder      # Add categories
php artisan db:seed --class=ProductSeeder       # Add test products
```

---

## Summary Commands for VPS

```bash
# 1. Pull changes
git pull origin main

# 2. Run migrations (safe - only new tables/columns)
sudo docker compose exec app php artisan migrate --force

# 3. Prepare images (if needed)
sudo docker compose exec app php artisan db:seed --class=PrepareTestImages

# 4. Seed categories (safe - skips existing)
sudo docker compose exec app php artisan db:seed --class=CategorySeeder

# 5. Seed products (safe - adds new)
sudo docker compose exec app php artisan db:seed --class=ProductSeeder

# 6. Clear cache
sudo docker compose exec app php artisan cache:clear
sudo docker compose exec app php artisan config:clear

# 7. Verify
curl http://167.86.87.72:8000/api/v1/products | jq '.meta.total'
```

---

## Troubleshooting

**Problem:** Categories already exist error

**Solution:** This is normal. Categories with duplicate names are skipped. Your existing categories remain unchanged.

---

**Problem:** Products not showing in API

**Solution:** Check verification status:

```bash
sudo docker compose exec app php artisan tinker --execute="
echo 'Approved products: ' . \App\Models\Product::where('verification_status', 'approved')->count() . PHP_EOL;
echo 'Total products: ' . \App\Models\Product::count() . PHP_EOL;
"
```

Update if needed:

```bash
sudo docker compose exec app php artisan tinker --execute="
\App\Models\Product::whereNull('verification_status')->update(['verification_status' => 'approved']);
echo 'Updated product verification status';
"
```

---

**Problem:** Images not loading

**Solution:**

```bash
# Recreate storage symlink
sudo docker compose exec app php artisan storage:link

# Check permissions
sudo docker compose exec app ls -lh storage/app/public/products/images/

# Rebuild nginx if needed
sudo docker compose build nginx && sudo docker compose up -d nginx
```

---

## ✅ Safe Production Workflow

1. **Always backup database before changes:**

   ```bash
   sudo docker compose exec postgres pg_dump -U postgres rented_db > backup_$(date +%Y%m%d).sql
   ```

2. **Test on staging first** (if available)

3. **Run migrations only** (never migrate:fresh)

4. **Seed specific seeders** (not DatabaseSeeder)

5. **Verify data after seeding**

6. **Monitor logs:**

   ```bash
   sudo docker compose logs -f app
   ```

Your existing users and data will remain completely untouched! 🎉
