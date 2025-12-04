<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AvatarUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_authenticated_user_can_upload_avatar(): void
    {
        $user = User::factory()->create();
        $avatar = UploadedFile::fake()->image('avatar.jpg', 500, 500)->size(1024);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/user/avatar', [
                'avatar' => $avatar,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'avatar_url',
                ],
            ])
            ->assertJson([
                'message' => 'Avatar updated successfully',
            ]);

        // Assert the file was stored
        $this->assertNotNull($user->fresh()->avatar_path);
        Storage::disk('public')->assertExists($user->fresh()->avatar_path);
    }

    public function test_avatar_upload_replaces_old_avatar(): void
    {
        $user = User::factory()->create();

        // Upload first avatar
        $firstAvatar = UploadedFile::fake()->image('first.jpg', 500, 500);
        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/user/avatar', ['avatar' => $firstAvatar]);

        $firstAvatarPath = $user->fresh()->avatar_path;
        Storage::disk('public')->assertExists($firstAvatarPath);

        // Upload second avatar
        $secondAvatar = UploadedFile::fake()->image('second.jpg', 500, 500);
        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/user/avatar', ['avatar' => $secondAvatar]);

        $secondAvatarPath = $user->fresh()->avatar_path;

        // Assert old avatar was deleted
        Storage::disk('public')->assertMissing($firstAvatarPath);
        // Assert new avatar exists
        Storage::disk('public')->assertExists($secondAvatarPath);
        // Assert paths are different
        $this->assertNotEquals($firstAvatarPath, $secondAvatarPath);
    }

    public function test_avatar_upload_requires_authentication(): void
    {
        $avatar = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->postJson('/api/v1/user/avatar', [
            'avatar' => $avatar,
        ]);

        $response->assertStatus(401);
    }

    public function test_avatar_upload_requires_image_file(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/user/avatar', [
                'avatar' => null,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    public function test_avatar_upload_validates_file_type(): void
    {
        $user = User::factory()->create();
        $invalidFile = UploadedFile::fake()->create('document.pdf', 1024);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/user/avatar', [
                'avatar' => $invalidFile,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    public function test_avatar_upload_validates_file_size(): void
    {
        $user = User::factory()->create();
        // Create file larger than 2MB
        $largeFile = UploadedFile::fake()->image('large.jpg', 3000, 3000)->size(3000);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/user/avatar', [
                'avatar' => $largeFile,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    public function test_avatar_upload_validates_dimensions(): void
    {
        $user = User::factory()->create();
        // Create image smaller than minimum dimensions (100x100)
        $smallImage = UploadedFile::fake()->image('small.jpg', 50, 50);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/user/avatar', [
                'avatar' => $smallImage,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    public function test_avatar_url_is_returned_in_response(): void
    {
        $user = User::factory()->create();
        $avatar = UploadedFile::fake()->image('avatar.jpg', 500, 500);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/user/avatar', [
                'avatar' => $avatar,
            ]);

        $avatarUrl = $response->json('data.avatar_url');

        $this->assertNotNull($avatarUrl);
        $this->assertStringContainsString('/storage/avatars/', $avatarUrl);
    }

    public function test_authenticated_user_can_delete_avatar(): void
    {
        $user = User::factory()->create();

        // First upload an avatar
        $avatar = UploadedFile::fake()->image('avatar.jpg', 500, 500);
        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/user/avatar', ['avatar' => $avatar]);

        $avatarPath = $user->fresh()->avatar_path;
        Storage::disk('public')->assertExists($avatarPath);

        // Delete the avatar
        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/v1/user/avatar');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Avatar deleted successfully',
            ]);

        // Assert avatar was removed from storage
        Storage::disk('public')->assertMissing($avatarPath);
        // Assert avatar_path is null in database
        $this->assertNull($user->fresh()->avatar_path);
    }

    public function test_delete_avatar_requires_authentication(): void
    {
        $response = $this->deleteJson('/api/v1/user/avatar');

        $response->assertStatus(401);
    }

    public function test_delete_avatar_when_no_avatar_exists(): void
    {
        $user = User::factory()->create(['avatar_path' => null]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/v1/user/avatar');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Avatar deleted successfully',
            ]);
    }

    public function test_avatar_url_accessor_returns_null_when_no_avatar(): void
    {
        $user = User::factory()->create(['avatar_path' => null]);

        $this->assertNull($user->avatar_url);
    }

    public function test_avatar_url_accessor_returns_full_url(): void
    {
        $user = User::factory()->create([
            'avatar_path' => 'avatars/test-avatar.jpg',
        ]);

        $avatarUrl = $user->avatar_url;

        $this->assertNotNull($avatarUrl);
        $this->assertStringContainsString('/storage/avatars/test-avatar.jpg', $avatarUrl);
    }
}
