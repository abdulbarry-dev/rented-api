# Copilot Instructions for Clean & Maintainable Laravel Project Structure

These guidelines ensure that all code generated for the **Rented Marketplace API** follows clean architecture principles, predictable patterns, and production-quality standards.

---

## 1. General Principles

1. **Follow Laravel best practices** at all times.
2. Write code that is **readable**, **scalable**, and **modular**.
3. Prefer **dependency injection**, **service classes**, and **repositories** over putting logic in controllers.
4. Never duplicate code; extract reusable logic.
5. All generated code must follow **PSR-12 coding standards**.
6. Avoid overly clever solutions—prefer simplicity and clarity.

---

## 2. Project Structure

Use the following structure for organization:

```
app/
 ├── Http/
 │    ├── Controllers/
 │    ├── Requests/
 │    └── Middleware/
 ├── Models/
 ├── Services/
 ├── Repositories/
 ├── Actions/
 ├── Policies/
 └── Traits/

routes/
 ├── api.php
 └── web.php

database/
 ├── migrations/
 ├── factories/
 └── seeders/
```

### Notes

* **Controllers**: Very thin; only coordinate requests.
* **Form Requests**: Contain validation only.
* **Services**: Contain business logic.
* **Repositories**: Handle database interactions.
* **Actions**: For single-purpose tasks.
* **Resources**: For consistent API formatting.

---

## 3. Controller Rules

* Controllers should be **skinny**.
* Each method should contain **no more than 10–15 lines**.
* No database logic inside controllers.
* Controllers must call:

  * **Form Request** classes for validation.
  * **Service** classes for business logic.
  * **Resource** classes for API response formatting.

**Example pattern:**

```php
public function store(StoreProductRequest $request, ProductService $service) {
    $product = $service->create($request->validated());
    return new ProductResource($product);
}
```

---

## 4. Validation Rules

* Always use **FormRequest** classes.
* Use descriptive rule sets.

Example:

```php
public function rules(): array
{
    return [
        'title' => 'required|string|max:255',
        'price' => 'required|numeric|min:1',
        'images' => 'nullable|array',
        'images.*' => 'image|max:2048'
    ];
}
```

---

## 5. Service Layer Rules

* Services contain all business logic.
* Keep each method focused on one responsibility.
* Services should never depend on controllers.
* Services may depend on repositories.

**Service example structure:**

```php
class ProductService
{
    public function __construct(private ProductRepository $repo) {}

    public function create(array $data) {
        return $repo->create($data);
    }
}
```

---

## 6. Repository Layer Rules

* All Eloquent queries must be inside repositories.
* Names must be consistent: `ProductRepository`, `UserRepository`, etc.
* Must return Eloquent models or collections.

```php
class ProductRepository
{
    public function create(array $data): Product
    {
        return Product::create($data);
    }
}
```

---

## 7. API Response Rules

Use **Laravel API Resources** for consistent formatting.

Example:

```php
class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'price_per_day' => $this->price_per_day,
            'thumbnail' => $this->thumbnail_url,
        ];
    }
}
```

---

## 8. Error Handling

* Use Laravel's exception handling.
* Never return raw error messages.
* Always return structured responses:

```json
{
  "message": "Validation failed.",
  "errors": { "title": ["Title is required."] }
}
```

---

## 9. Route Organization

* Group routes by feature.
* Use route prefixes and middleware.

Example:

```php
Route::middleware(['auth:sanctum'])->group(function() {
    Route::prefix('products')->group(function() {
        Route::post('/', [ProductController::class, 'store']);
    });
});
```

---

## 10. Database & Migrations Rules

* Always use proper foreign keys.
* Use enums or tinyints for statuses.
* Never store unvalidated JSON.

---

## 11. Naming Conventions

* Controllers: `SomethingController`
* Requests: `StoreSomethingRequest`, `UpdateSomethingRequest`
* Services: `SomethingService`
* Repositories: `SomethingRepository`
* Resources: `SomethingResource`

---

## 12. Comments & Documentation

* Document complex logic inside services only.
* Avoid comments for obvious code.
* Use PHPDoc for type clarity.

---

## 13. Security Rules

* Never trust input—always validate.
* Protect all modifying routes with Sanctum.
* Ensure authorization using policies when needed.
* Guard file uploads.

---

## 14. Additional Performance Guidelines

* Use eager loading when appropriate.
* Cache heavy queries.
* Never load unnecessary relationships.
* Prefer `chunk()` for processing large datasets.

---

## 15. Testing Guidelines

* Generate tests for:

  * Controllers (feature tests)
  * Services (unit tests)
  * Repositories (unit tests)
* Use factories for seeding test data.

---

## Summary

By following these rules, Copilot will generate:

* Clean
* Maintainable
* Scalable
* Professional

Laravel code suitable for long-term development and a production-level marketplace API.
