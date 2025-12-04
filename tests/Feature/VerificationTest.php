<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserVerification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function user_can_submit_verification_documents_with_all_required_files(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/verify', [
            'id_front' => UploadedFile::fake()->image('id_front.jpg', 800, 600),
            'id_back' => UploadedFile::fake()->image('id_back.jpg', 800, 600),
            'selfie' => UploadedFile::fake()->image('selfie.jpg', 800, 600),
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'user_id',
                'status',
                'submitted_at',
            ],
        ]);

        $this->assertDatabaseHas('user_verifications', [
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function submitted_images_are_stored_in_json_field(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/v1/verify', [
            'id_front' => UploadedFile::fake()->image('id_front.jpg'),
            'id_back' => UploadedFile::fake()->image('id_back.jpg'),
            'selfie' => UploadedFile::fake()->image('selfie.jpg'),
        ]);

        $verification = UserVerification::where('user_id', $user->id)->first();

        $this->assertNotNull($verification->verification_images);
        $this->assertIsArray($verification->verification_images);
        $this->assertArrayHasKey('id_front', $verification->verification_images);
        $this->assertArrayHasKey('id_back', $verification->verification_images);
        $this->assertArrayHasKey('selfie', $verification->verification_images);
    }

    /** @test */
    public function verification_requires_id_front_image(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/verify', [
            'id_back' => UploadedFile::fake()->image('id_back.jpg'),
            'selfie' => UploadedFile::fake()->image('selfie.jpg'),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['id_front']);
    }

    /** @test */
    public function verification_requires_id_back_image(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/verify', [
            'id_front' => UploadedFile::fake()->image('id_front.jpg'),
            'selfie' => UploadedFile::fake()->image('selfie.jpg'),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['id_back']);
    }

    /** @test */
    public function verification_requires_selfie_image(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/verify', [
            'id_front' => UploadedFile::fake()->image('id_front.jpg'),
            'id_back' => UploadedFile::fake()->image('id_back.jpg'),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['selfie']);
    }

    /** @test */
    public function verification_rejects_files_larger_than_5mb(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/verify', [
            'id_front' => UploadedFile::fake()->image('id_front.jpg')->size(6000), // 6MB
            'id_back' => UploadedFile::fake()->image('id_back.jpg'),
            'selfie' => UploadedFile::fake()->image('selfie.jpg'),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['id_front']);
    }

    /** @test */
    public function verification_only_accepts_jpeg_jpg_png_formats(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/verify', [
            'id_front' => UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf'),
            'id_back' => UploadedFile::fake()->image('id_back.jpg'),
            'selfie' => UploadedFile::fake()->image('selfie.jpg'),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['id_front']);
    }

    /** @test */
    public function verification_rejects_images_smaller_than_minimum_dimensions(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/verify', [
            'id_front' => UploadedFile::fake()->image('id_front.jpg', 100, 100), // Too small
            'id_back' => UploadedFile::fake()->image('id_back.jpg'),
            'selfie' => UploadedFile::fake()->image('selfie.jpg'),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['id_front']);
    }

    /** @test */
    public function user_cannot_submit_verification_if_already_verified(): void
    {
        $user = User::factory()->create([
            'verification_status' => 'verified',
        ]);

        UserVerification::create([
            'user_id' => $user->id,
            'verification_images' => [
                'id_front' => 'path/to/front.jpg',
                'id_back' => 'path/to/back.jpg',
                'selfie' => 'path/to/selfie.jpg',
            ],
            'status' => 'verified',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/verify', [
            'id_front' => UploadedFile::fake()->image('id_front.jpg'),
            'id_back' => UploadedFile::fake()->image('id_back.jpg'),
            'selfie' => UploadedFile::fake()->image('selfie.jpg'),
        ]);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'User is already verified.']);
    }

    /** @test */
    public function user_cannot_submit_verification_if_pending_review(): void
    {
        $user = User::factory()->create();

        UserVerification::create([
            'user_id' => $user->id,
            'verification_images' => [
                'id_front' => 'path/to/front.jpg',
                'id_back' => 'path/to/back.jpg',
                'selfie' => 'path/to/selfie.jpg',
            ],
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/verify', [
            'id_front' => UploadedFile::fake()->image('id_front.jpg'),
            'id_back' => UploadedFile::fake()->image('id_back.jpg'),
            'selfie' => UploadedFile::fake()->image('selfie.jpg'),
        ]);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'A verification request is already pending review.']);
    }

    /** @test */
    public function user_can_get_verification_status(): void
    {
        $user = User::factory()->create([
            'verification_status' => 'pending',
        ]);

        UserVerification::create([
            'user_id' => $user->id,
            'verification_images' => [
                'id_front' => 'verifications/national-ids/front.jpg',
                'id_back' => 'verifications/national-ids/back.jpg',
                'selfie' => 'verifications/selfies/selfie.jpg',
            ],
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/verify/status');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'status',
                'verified_at',
                'rejection_reason',
                'has_submitted_documents',
                'document_status',
                'submitted_at',
                'images',
            ],
        ]);
        $response->assertJson([
            'data' => [
                'status' => 'pending',
                'has_submitted_documents' => true,
            ],
        ]);
    }

    /** @test */
    public function verification_status_includes_image_urls(): void
    {
        $user = User::factory()->create();

        UserVerification::create([
            'user_id' => $user->id,
            'verification_images' => [
                'id_front' => 'verifications/national-ids/front.jpg',
                'id_back' => 'verifications/national-ids/back.jpg',
                'selfie' => 'verifications/selfies/selfie.jpg',
            ],
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/verify/status');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'images' => [
                    'id_front_url',
                    'id_back_url',
                    'selfie_url',
                ],
            ],
        ]);
    }

    /** @test */
    public function guest_cannot_submit_verification(): void
    {
        $response = $this->postJson('/api/v1/verify', [
            'id_front' => UploadedFile::fake()->image('id_front.jpg'),
            'id_back' => UploadedFile::fake()->image('id_back.jpg'),
            'selfie' => UploadedFile::fake()->image('selfie.jpg'),
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function guest_cannot_view_verification_status(): void
    {
        $response = $this->getJson('/api/v1/verify/status');

        $response->assertStatus(401);
    }

    /** @test */
    public function verification_model_has_correct_accessor_for_id_front_path(): void
    {
        $verification = UserVerification::factory()->create([
            'verification_images' => [
                'id_front' => 'verifications/national-ids/front.jpg',
                'id_back' => 'verifications/national-ids/back.jpg',
                'selfie' => 'verifications/selfies/selfie.jpg',
            ],
        ]);

        $this->assertEquals('verifications/national-ids/front.jpg', $verification->id_front_path);
    }

    /** @test */
    public function verification_model_has_correct_accessor_for_id_back_path(): void
    {
        $verification = UserVerification::factory()->create([
            'verification_images' => [
                'id_front' => 'verifications/national-ids/front.jpg',
                'id_back' => 'verifications/national-ids/back.jpg',
                'selfie' => 'verifications/selfies/selfie.jpg',
            ],
        ]);

        $this->assertEquals('verifications/national-ids/back.jpg', $verification->id_back_path);
    }

    /** @test */
    public function verification_model_has_correct_accessor_for_selfie_path(): void
    {
        $verification = UserVerification::factory()->create([
            'verification_images' => [
                'id_front' => 'verifications/national-ids/front.jpg',
                'id_back' => 'verifications/national-ids/back.jpg',
                'selfie' => 'verifications/selfies/selfie.jpg',
            ],
        ]);

        $this->assertEquals('verifications/selfies/selfie.jpg', $verification->selfie_path);
    }

    /** @test */
    public function verification_model_status_helpers_work_correctly(): void
    {
        $pendingVerification = UserVerification::factory()->create(['status' => 'pending']);
        $verifiedVerification = UserVerification::factory()->create(['status' => 'verified']);
        $rejectedVerification = UserVerification::factory()->create(['status' => 'rejected']);

        $this->assertTrue($pendingVerification->isPending());
        $this->assertFalse($pendingVerification->isVerified());
        $this->assertFalse($pendingVerification->isRejected());

        $this->assertFalse($verifiedVerification->isPending());
        $this->assertTrue($verifiedVerification->isVerified());
        $this->assertFalse($verifiedVerification->isRejected());

        $this->assertFalse($rejectedVerification->isPending());
        $this->assertFalse($rejectedVerification->isVerified());
        $this->assertTrue($rejectedVerification->isRejected());
    }

    /** @test */
    public function verification_service_can_approve_verification(): void
    {
        $user = User::factory()->create();
        $verification = UserVerification::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $verificationService = app(\App\Services\VerificationService::class);
        $verificationService->approveVerification($verification);

        $verification->refresh();
        $user->refresh();

        $this->assertEquals('verified', $verification->status);
        $this->assertNotNull($verification->reviewed_at);
        $this->assertEquals('verified', $user->verification_status);
        $this->assertNotNull($user->verified_at);
    }

    /** @test */
    public function verification_service_can_reject_verification(): void
    {
        $user = User::factory()->create();
        $verification = UserVerification::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $reason = 'ID photo is blurry';
        $verificationService = app(\App\Services\VerificationService::class);
        $verificationService->rejectVerification($verification, $reason);

        $verification->refresh();
        $user->refresh();

        $this->assertEquals('rejected', $verification->status);
        $this->assertEquals($reason, $verification->admin_notes);
        $this->assertNotNull($verification->reviewed_at);
        $this->assertEquals('rejected', $user->verification_status);
        $this->assertEquals($reason, $user->rejection_reason);
    }
}
