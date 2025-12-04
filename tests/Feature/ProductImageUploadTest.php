<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_authenticated_user_can_create_product_with_images(): void
    {
        $user = User::factory()->create(['verification_status' => 'verified']);
        $category = Category::factory()->create();

        $thumbnail = UploadedFile::fake()->image('thumbnail.jpg', 800, 600);
        $images = [
            UploadedFile::fake()->image('image1.jpg', 1024, 768),
            UploadedFile::fake()->image('image2.jpg', 1024, 768),
            UploadedFile::fake()->image('image3.jpg', 1024, 768),
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/products', [
                'category_id' => $category->id,
                'title' => 'Test Product',
                'description' => 'This is a test product description',
                'price_per_day' => 50.00,
                'thumbnail' => $thumbnail,
                'images' => $images,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'price_per_day',
                    'thumbnail_url',
                    'image_urls',
                ],
            ])
            ->assertJson([
                'message' => 'Product created successfully',
            ]);

        $product = Product::latest()->first();
        $this->assertNotNull($product->thumbnail);
        $this->assertIsArray($product->images);
        $this->assertCount(3, $product->images);

        // Assert files were stored
        Storage::disk('public')->assertExists($product->thumbnail);
        foreach ($product->images as $imagePath) {
            Storage::disk('public')->assertExists($imagePath);
        }
    }

    public function test_product_can_be_created_with_up_to_five_images(): void
    {
        $user = User::factory()->create(['verification_status' => 'verified']);
        $category = Category::factory()->create();

        $images = [
            UploadedFile::fake()->image('image1.jpg'),
            UploadedFile::fake()->image('image2.jpg'),
            UploadedFile::fake()->image('image3.jpg'),
            UploadedFile::fake()->image('image4.jpg'),
            UploadedFile::fake()->image('image5.jpg'),
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/products', [
                'category_id' => $category->id,
                'title' => 'Test Product',
                'description' => 'Test description',
                'price_per_day' => 50.00,
                'images' => $images,
            ]);

        $response->assertStatus(201);

        $product = Product::latest()->first();
        $this->assertCount(5, $product->images);
    }

    public function test_product_creation_validates_max_five_images(): void
    {
        $user = User::factory()->create(['verification_status' => 'verified']);
        $category = Category::factory()->create();

        $images = [
            UploadedFile::fake()->image('image1.jpg'),
            UploadedFile::fake()->image('image2.jpg'),
            UploadedFile::fake()->image('image3.jpg'),
            UploadedFile::fake()->image('image4.jpg'),
            UploadedFile::fake()->image('image5.jpg'),
            UploadedFile::fake()->image('image6.jpg'), // Exceeds limit
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/products', [
                'category_id' => $category->id,
                'title' => 'Test Product',
                'description' => 'Test description',
                'price_per_day' => 50.00,
                'images' => $images,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['images']);
    }

    public function test_product_image_upload_validates_file_type(): void
    {
        $user = User::factory()->create(['verification_status' => 'verified']);
        $category = Category::factory()->create();

        $invalidFile = UploadedFile::fake()->create('document.pdf', 1024);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/products', [
                'category_id' => $category->id,
                'title' => 'Test Product',
                'description' => 'Test description',
                'price_per_day' => 50.00,
                'images' => [$invalidFile],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['images.0']);
    }

    public function test_product_image_upload_validates_file_size(): void
    {
        $user = User::factory()->create(['verification_status' => 'verified']);
        $category = Category::factory()->create();

        $largeFile = UploadedFile::fake()->image('large.jpg', 3000, 3000)->size(3000);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/products', [
                'category_id' => $category->id,
                'title' => 'Test Product',
                'description' => 'Test description',
                'price_per_day' => 50.00,
                'images' => [$largeFile],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['images.0']);
    }

    public function test_product_can_be_created_without_images(): void
    {
        $user = User::factory()->create(['verification_status' => 'verified']);
        $category = Category::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/products', [
                'category_id' => $category->id,
                'title' => 'Test Product',
                'description' => 'Test description',
                'price_per_day' => 50.00,
            ]);

        $response->assertStatus(201);

        $product = Product::latest()->first();
        $this->assertNull($product->thumbnail);
        $this->assertEmpty($product->images ?? []);
    }

    public function test_product_owner_can_update_product_images(): void
    {
        $user = User::factory()->create(['verification_status' => 'verified']);
        $category = Category::factory()->create();

        // Create product with images
        $product = Product::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'thumbnail' => 'products/thumbnails/old-thumb.jpg',
            'images' => ['products/images/old-image1.jpg', 'products/images/old-image2.jpg'],
        ]);

        // Fake the old files in storage
        Storage::disk('public')->put('products/thumbnails/old-thumb.jpg', 'old thumbnail');
        Storage::disk('public')->put('products/images/old-image1.jpg', 'old image 1');
        Storage::disk('public')->put('products/images/old-image2.jpg', 'old image 2');

        // Update with new images
        $newThumbnail = UploadedFile::fake()->image('new-thumb.jpg');
        $newImages = [
            UploadedFile::fake()->image('new-image1.jpg'),
            UploadedFile::fake()->image('new-image2.jpg'),
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/products/{$product->id}", [
                'thumbnail' => $newThumbnail,
                'images' => $newImages,
            ]);

        $response->assertStatus(200);

        $product->refresh();

        // Assert old files were deleted
        Storage::disk('public')->assertMissing('products/thumbnails/old-thumb.jpg');
        Storage::disk('public')->assertMissing('products/images/old-image1.jpg');
        Storage::disk('public')->assertMissing('products/images/old-image2.jpg');

        // Assert new files exist
        Storage::disk('public')->assertExists($product->thumbnail);
        foreach ($product->images as $imagePath) {
            Storage::disk('public')->assertExists($imagePath);
        }
    }

    public function test_product_deletion_removes_all_images(): void
    {
        $user = User::factory()->create(['verification_status' => 'verified']);
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'thumbnail' => 'products/thumbnails/thumb.jpg',
            'images' => ['products/images/image1.jpg', 'products/images/image2.jpg'],
        ]);

        // Create fake files
        Storage::disk('public')->put('products/thumbnails/thumb.jpg', 'thumbnail');
        Storage::disk('public')->put('products/images/image1.jpg', 'image 1');
        Storage::disk('public')->put('products/images/image2.jpg', 'image 2');

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Product deleted successfully',
            ]);

        // Assert all files were deleted
        Storage::disk('public')->assertMissing('products/thumbnails/thumb.jpg');
        Storage::disk('public')->assertMissing('products/images/image1.jpg');
        Storage::disk('public')->assertMissing('products/images/image2.jpg');
    }

    public function test_thumbnail_url_accessor_returns_null_when_no_thumbnail(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'thumbnail' => null,
        ]);

        $this->assertNull($product->thumbnail_url);
    }

    public function test_thumbnail_url_accessor_returns_full_url(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'thumbnail' => 'products/thumbnails/test-thumb.jpg',
        ]);

        $thumbnailUrl = $product->thumbnail_url;

        $this->assertNotNull($thumbnailUrl);
        $this->assertStringContainsString('/storage/products/thumbnails/test-thumb.jpg', $thumbnailUrl);
    }

    public function test_image_urls_accessor_returns_empty_array_when_no_images(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'images' => null,
        ]);

        $this->assertIsArray($product->image_urls);
        $this->assertEmpty($product->image_urls);
    }

    public function test_image_urls_accessor_returns_full_urls(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'images' => [
                'products/images/image1.jpg',
                'products/images/image2.jpg',
                'products/images/image3.jpg',
            ],
        ]);

        $imageUrls = $product->image_urls;

        $this->assertIsArray($imageUrls);
        $this->assertCount(3, $imageUrls);

        foreach ($imageUrls as $url) {
            $this->assertStringContainsString('/storage/products/images/', $url);
        }
    }

    public function test_product_response_includes_image_urls(): void
    {
        $user = User::factory()->create(['verification_status' => 'verified']);
        $category = Category::factory()->create();

        $thumbnail = UploadedFile::fake()->image('thumbnail.jpg');
        $images = [
            UploadedFile::fake()->image('image1.jpg'),
            UploadedFile::fake()->image('image2.jpg'),
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/products', [
                'category_id' => $category->id,
                'title' => 'Test Product',
                'description' => 'Test description',
                'price_per_day' => 50.00,
                'thumbnail' => $thumbnail,
                'images' => $images,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'thumbnail_url',
                    'image_urls',
                ],
            ]);

        $data = $response->json('data');

        $this->assertNotNull($data['thumbnail_url']);
        $this->assertStringContainsString('/storage/products/thumbnails/', $data['thumbnail_url']);

        $this->assertIsArray($data['image_urls']);
        $this->assertCount(2, $data['image_urls']);

        foreach ($data['image_urls'] as $url) {
            $this->assertStringContainsString('/storage/products/images/', $url);
        }
    }

    public function test_non_owner_cannot_update_product_images(): void
    {
        $owner = User::factory()->create(['verification_status' => 'verified']);
        $otherUser = User::factory()->create(['verification_status' => 'verified']);
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
        ]);

        $newImages = [UploadedFile::fake()->image('new.jpg')];

        $response = $this->actingAs($otherUser, 'sanctum')
            ->putJson("/api/v1/products/{$product->id}", [
                'images' => $newImages,
            ]);

        $response->assertStatus(403);
    }

    public function test_non_owner_cannot_delete_product(): void
    {
        $owner = User::factory()->create(['verification_status' => 'verified']);
        $otherUser = User::factory()->create(['verification_status' => 'verified']);
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($otherUser, 'sanctum')
            ->deleteJson("/api/v1/products/{$product->id}");

        $response->assertStatus(403);
    }

    public function test_unverified_user_cannot_create_product(): void
    {
        $user = User::factory()->create(['verification_status' => 'pending']);
        $category = Category::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/products', [
                'category_id' => $category->id,
                'title' => 'Test Product',
                'description' => 'Test description',
                'price_per_day' => 50.00,
            ]);

        $response->assertStatus(403);
    }
}
