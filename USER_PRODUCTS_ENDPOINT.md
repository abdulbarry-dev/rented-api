# User Products Endpoint Logic

This document explains the logic and behavior of the `/api/v1/user/products` endpoint and how it differs from public product endpoints.

---

## Endpoint Overview

**URL:** `GET /api/v1/user/products`

**Authentication:** Required (`auth:sanctum` middleware)

**Authorization:** None (any authenticated user can access their own products)

**Purpose:** Allows users to view all their own products regardless of verification status or availability

---

## Key Differences from Public Endpoints

### Public Product Endpoints (`/api/v1/products`)

```php
// GET /api/v1/products - List all products
// GET /api/v1/products/{id} - Get single product

Filters applied:
✅ verification_status = 'approved'
✅ is_available = true
```

**Result:** Only shows products that are approved by admins and marked as available.

### User Products Endpoint (`/api/v1/user/products`)

```php
// GET /api/v1/user/products

Filters applied:
✅ user_id = authenticated user's ID
❌ NO verification_status filter
❌ NO is_available filter
```

**Result:** Shows ALL products belonging to the authenticated user, including:
- ✅ Approved products
- ✅ Pending products (awaiting admin review)
- ✅ Rejected products (failed admin review)
- ✅ Draft products (not yet submitted)
- ✅ Available products
- ✅ Unavailable products (marked as unavailable by user)

---

## Request Flow

### 1. Controller Layer
**File:** `app/Http/Controllers/Api/ProductController.php`

```php
public function userProducts(Request $request): JsonResponse
{
    // Get authenticated user from request
    $products = $this->service->getUserProducts($request->user());

    return response()->json([
        'data' => ProductResource::collection($products),
    ]);
}
```

**Responsibilities:**
- Extract authenticated user from request
- Call service layer to retrieve products
- Transform products using `ProductResource`
- Return JSON response

---

### 2. Service Layer
**File:** `app/Services/ProductService.php`

```php
public function getUserProducts(User $user): Collection
{
    return $this->repository->getByUserId($user->id);
}
```

**Responsibilities:**
- Accept `User` model as parameter
- Delegate to repository layer
- Return `Collection` of products

---

### 3. Repository Layer
**File:** `app/Repositories/ProductRepository.php`

```php
public function getByUserId(int $userId): Collection
{
    return Product::with('category')
        ->where('user_id', $userId)
        ->latest()
        ->get();
}
```

**Responsibilities:**
- Query database for products owned by user
- Eager load `category` relationship (performance optimization)
- Order by most recent first (`latest()`)
- Return all products without filtering by status

**Note:** No caching is applied to user products to ensure real-time data.

---

## Why No Verification Filtering?

### Business Logic Reasoning

1. **Dashboard Visibility**
   - Users need to see all their products in their dashboard
   - They need to know which products are pending review
   - They need to see rejection reasons for rejected products
   - They need to manage draft products before submission

2. **Product Management**
   - Users can edit products that are pending or rejected
   - Users can delete products that were rejected
   - Users can toggle availability on approved products
   - Users need full visibility of their inventory

3. **Transparency**
   - Users should know the status of their submissions
   - Clear feedback loop for rejected products
   - Ability to resubmit or fix rejected products

---

## Use Cases

### Use Case 1: User Dashboard
**Scenario:** User logs in and wants to see all their products

**Request:**
```bash
curl -X GET http://localhost:8000/api/v1/user/products \
  -H "Authorization: Bearer {token}"
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "MacBook Pro 16",
      "verification_status": "approved",
      "is_available": true,
      ...
    },
    {
      "id": 2,
      "title": "Canon Camera",
      "verification_status": "pending",
      "is_available": true,
      ...
    },
    {
      "id": 3,
      "title": "Old Laptop",
      "verification_status": "rejected",
      "is_available": false,
      ...
    }
  ]
}
```

**Frontend Logic:**
```javascript
// Group products by status
const approvedProducts = products.filter(p => p.verification_status === 'approved');
const pendingProducts = products.filter(p => p.verification_status === 'pending');
const rejectedProducts = products.filter(p => p.verification_status === 'rejected');

// Show different UI for each status
approvedProducts.forEach(p => {
  // Show "Edit" and "Mark Unavailable" buttons
});

pendingProducts.forEach(p => {
  // Show "Under Review" badge
});

rejectedProducts.forEach(p => {
  // Show "Rejected" badge and "View Reason" button
});
```

---

### Use Case 2: Product Inventory Management
**Scenario:** User wants to edit a rejected product

**Step 1:** Get all products
```
GET /api/v1/user/products
```

**Step 2:** Filter rejected products on frontend
```javascript
const rejectedProduct = products.find(p => 
  p.id === 3 && p.verification_status === 'rejected'
);
```

**Step 3:** Edit product
```
PUT /api/v1/products/3
Body: { title: "Updated Title", description: "Fixed issues" }
```

**Step 4:** Product goes back to "pending" status for re-review

---

### Use Case 3: Temporarily Unavailable Products
**Scenario:** User wants to hide a product temporarily (out of stock, maintenance, etc.)

**Request:**
```
PUT /api/v1/products/{id}
Body: { is_available: false }
```

**Result:**
- Product still appears in `/api/v1/user/products` ✅
- Product disappears from `/api/v1/products` ❌
- User can toggle back to available later

---

## Comparison Table

| Feature | Public Endpoints | User Products Endpoint |
|---------|-----------------|----------------------|
| **URL** | `/api/v1/products` | `/api/v1/user/products` |
| **Authentication** | Optional | Required |
| **Shows Approved Products** | ✅ Yes | ✅ Yes |
| **Shows Pending Products** | ❌ No | ✅ Yes |
| **Shows Rejected Products** | ❌ No | ✅ Yes |
| **Shows Unavailable Products** | ❌ No | ✅ Yes |
| **Filtering** | Status + Availability | Owner Only |
| **Caching** | ✅ 10 minutes | ❌ No cache |
| **Purpose** | Public browsing | Owner management |

---

## Response Format

### ProductResource Transformation

**File:** `app/Http/Resources/ProductResource.php`

```php
return [
    'id' => $this->id,
    'title' => $this->title,
    'description' => $this->description,
    'price_per_day' => $this->price_per_day,
    'is_for_sale' => $this->is_for_sale,
    'sale_price' => $this->sale_price,
    'is_available' => $this->is_available,
    'verification_status' => $this->verification_status, // 'pending', 'approved', 'rejected'
    'thumbnail_url' => $this->thumbnail_url,
    'image_urls' => $this->image_urls,
    'category' => new CategoryResource($this->whenLoaded('category')),
    'owner' => new UserResource($this->whenLoaded('user')),
    'created_at' => $this->created_at,
    'updated_at' => $this->updated_at,
];
```

**Note:** The `verification_status` field is included in the response, allowing frontends to show appropriate UI based on product status.

---

## Security Considerations

### Why This Approach is Safe

1. **Owner-Only Access**
   ```php
   ->where('user_id', $userId)
   ```
   Users can only see their own products, not other users' products.

2. **Authentication Required**
   ```php
   Route::middleware('auth:sanctum')->group(function () {
       Route::get('/user/products', [ProductController::class, 'userProducts']);
   });
   ```
   Must provide valid authentication token.

3. **No Information Leakage**
   - Rejected/pending products are not exposed in public endpoints
   - Users cannot see rejection reasons for other users' products
   - Admin actions (approve/reject) are logged separately

---

## Performance Considerations

### No Caching

User products are not cached because:
1. **Real-time Updates:** Users expect immediate feedback after creating/editing products
2. **Status Changes:** Admin approval/rejection should reflect immediately
3. **Small Dataset:** Each user typically has few products (<100)
4. **Infrequent Access:** Dashboard is not accessed as frequently as public listings

### Optimization: Eager Loading

```php
->with('category')
```

Prevents N+1 query problem by loading categories in a single query.

**Without eager loading:**
- 1 query to get products
- N queries to get categories (one per product)
- Total: N+1 queries

**With eager loading:**
- 1 query to get products
- 1 query to get all categories
- Total: 2 queries

---

## Frontend Integration Examples

### React Example

```javascript
import { useState, useEffect } from 'react';

function UserDashboard() {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch('http://api.example.com/api/v1/user/products', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      }
    })
    .then(res => res.json())
    .then(data => {
      setProducts(data.data);
      setLoading(false);
    });
  }, []);

  const approvedProducts = products.filter(p => p.verification_status === 'approved');
  const pendingProducts = products.filter(p => p.verification_status === 'pending');
  const rejectedProducts = products.filter(p => p.verification_status === 'rejected');

  return (
    <div>
      <h2>Approved Products ({approvedProducts.length})</h2>
      {approvedProducts.map(product => (
        <ProductCard key={product.id} product={product} status="approved" />
      ))}

      <h2>Pending Review ({pendingProducts.length})</h2>
      {pendingProducts.map(product => (
        <ProductCard key={product.id} product={product} status="pending" />
      ))}

      <h2>Rejected ({rejectedProducts.length})</h2>
      {rejectedProducts.map(product => (
        <ProductCard key={product.id} product={product} status="rejected" />
      ))}
    </div>
  );
}
```

### Flutter Example

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class ProductService {
  Future<List<Product>> getUserProducts(String token) async {
    final response = await http.get(
      Uri.parse('http://api.example.com/api/v1/user/products'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      return (data['data'] as List)
          .map((json) => Product.fromJson(json))
          .toList();
    } else {
      throw Exception('Failed to load products');
    }
  }
}

class DashboardScreen extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return FutureBuilder<List<Product>>(
      future: ProductService().getUserProducts(authToken),
      builder: (context, snapshot) {
        if (!snapshot.hasData) return CircularProgressIndicator();
        
        final products = snapshot.data!;
        final approved = products.where((p) => p.verificationStatus == 'approved').toList();
        final pending = products.where((p) => p.verificationStatus == 'pending').toList();
        final rejected = products.where((p) => p.verificationStatus == 'rejected').toList();

        return ListView(
          children: [
            SectionHeader(title: 'Approved Products', count: approved.length),
            ...approved.map((p) => ProductTile(product: p)),
            
            SectionHeader(title: 'Pending Review', count: pending.length),
            ...pending.map((p) => ProductTile(product: p, badge: 'Under Review')),
            
            SectionHeader(title: 'Rejected', count: rejected.length),
            ...rejected.map((p) => ProductTile(product: p, badge: 'Rejected')),
          ],
        );
      },
    );
  }
}
```

---

## Related Endpoints

### Get Single Product (With Owner Override)

**Endpoint:** `GET /api/v1/products/{id}`

**Special Behavior:**
If the product is not approved but the requester is the owner, the product is still returned.

```php
// Public user trying to access unapproved product
GET /api/v1/products/123
Response: 404 Not Found

// Owner trying to access their own unapproved product
GET /api/v1/products/123
Authorization: Bearer {owner_token}
Response: 200 OK (product data returned)
```

This allows owners to preview their products even if not yet approved.

---

## Error Scenarios

### 1. Unauthenticated Request

**Request:**
```bash
curl http://localhost:8000/api/v1/user/products
```

**Response:**
```json
{
  "message": "Unauthenticated."
}
```
**Status Code:** 401

---

### 2. Invalid/Expired Token

**Request:**
```bash
curl http://localhost:8000/api/v1/user/products \
  -H "Authorization: Bearer invalid_token"
```

**Response:**
```json
{
  "message": "Unauthenticated."
}
```
**Status Code:** 401

---

### 3. No Products Found

**Request:**
```bash
curl http://localhost:8000/api/v1/user/products \
  -H "Authorization: Bearer {valid_token}"
```

**Response:**
```json
{
  "data": []
}
```
**Status Code:** 200

**Note:** Empty array is returned, not 404. This is correct behavior.

---

## Testing

### Manual Testing with cURL

```bash
# 1. Login to get token
TOKEN=$(curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}' \
  | jq -r '.token')

# 2. Get user products
curl http://localhost:8000/api/v1/user/products \
  -H "Authorization: Bearer $TOKEN" \
  | jq '.'

# 3. Verify all statuses are included
curl http://localhost:8000/api/v1/user/products \
  -H "Authorization: Bearer $TOKEN" \
  | jq '.data[] | {id, title, verification_status, is_available}'
```

### Automated Testing (PHPUnit)

```php
public function test_user_can_see_all_their_products_regardless_of_status()
{
    $user = User::factory()->create();
    
    // Create products with different statuses
    $approved = Product::factory()->create([
        'user_id' => $user->id,
        'verification_status' => 'approved',
    ]);
    
    $pending = Product::factory()->create([
        'user_id' => $user->id,
        'verification_status' => 'pending',
    ]);
    
    $rejected = Product::factory()->create([
        'user_id' => $user->id,
        'verification_status' => 'rejected',
    ]);
    
    // Create another user's product (should not appear)
    $otherProduct = Product::factory()->create([
        'user_id' => User::factory()->create()->id,
        'verification_status' => 'approved',
    ]);
    
    $response = $this->actingAs($user)
        ->getJson('/api/v1/user/products');
    
    $response->assertStatus(200)
        ->assertJsonCount(3, 'data')
        ->assertJsonFragment(['id' => $approved->id])
        ->assertJsonFragment(['id' => $pending->id])
        ->assertJsonFragment(['id' => $rejected->id])
        ->assertJsonMissing(['id' => $otherProduct->id]);
}
```

---

## Summary

The `/api/v1/user/products` endpoint is designed for **owner dashboard functionality** where users need full visibility of their product inventory, including products that are pending review, rejected, or marked unavailable. This differs from public endpoints which only show approved and available products to maintain marketplace quality.

**Key Takeaways:**
- ✅ Shows ALL user's products regardless of status
- ✅ Authentication required
- ✅ Owner-only access (security)
- ✅ No caching (real-time data)
- ✅ Eager loading for performance
- ✅ Supports product management workflows
