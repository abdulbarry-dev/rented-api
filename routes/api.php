<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\DisputeController;
use App\Http\Controllers\Api\FavouriteController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\RentalAvailabilityController;
use App\Http\Controllers\Api\RentalController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SocialAuthController;
use App\Http\Controllers\Api\VerificationController;
use Illuminate\Support\Facades\Route;

// API Status endpoint
Route::get('/', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API is working',
        'version' => 'v1',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Phase 2 - Authentication (No Authentication Required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Google OAuth
Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);

// Password Reset (No Authentication Required)
Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

// Phase 1 - Public API (No Authentication Required)
// Products - Read-only public endpoints
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// Categories - Read-only public endpoints
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);

// Reviews - Public read endpoints
Route::get('/products/{productId}/reviews', [ReviewController::class, 'productReviews']);
Route::get('/products/{productId}/rating', [ReviewController::class, 'productRatingStats']);

// Rental Availability - Public read
Route::get('/products/{productId}/availability', [RentalAvailabilityController::class, 'index']);
Route::post('/products/{productId}/check-availability', [RentalAvailabilityController::class, 'checkAvailability']);

// Protected Routes (Require Authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Phase 6 - User Profile Management
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::post('/user/avatar', [AuthController::class, 'updateAvatar']);
    Route::delete('/user/avatar', [AuthController::class, 'deleteAvatar']);

    // Phase 3 - User Verification
    Route::post('/verify', [VerificationController::class, 'upload']);
    Route::get('/verify/status', [VerificationController::class, 'status']);

    // Secure verification image viewing (owner only, rate limited)
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/verify/image/{imageType}', [VerificationController::class, 'viewImage']);
    });

    // Phase 4 - Product Management CRUD (Verified Users Only)
    Route::middleware('verified')->group(function () {
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    });

    // User's Products (Authentication Required, No Verification Required)
    Route::get('/user/products', [ProductController::class, 'userProducts']);

    // Phase 5 - Renting & Buying Flow (Authenticated Users)
    // Rentals
    Route::post('/rentals', [RentalController::class, 'store']);
    Route::put('/rentals/{id}', [RentalController::class, 'update']);
    Route::get('/user/rentals', [RentalController::class, 'userRentals']);
    Route::get('/products/{productId}/rentals', [RentalController::class, 'productRentals']);

    // Purchases
    Route::post('/purchases', [PurchaseController::class, 'store']);
    Route::put('/purchases/{id}/complete', [PurchaseController::class, 'complete']);
    Route::put('/purchases/{id}/cancel', [PurchaseController::class, 'cancel']);
    Route::get('/user/purchases', [PurchaseController::class, 'userPurchases']);

    // Reviews (Authenticated Users)
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
    Route::get('/user/reviews', [ReviewController::class, 'userReviews']);

    // Favourites (Authenticated Users)
    Route::get('/favourites', [FavouriteController::class, 'index']);
    Route::post('/favourites/toggle', [FavouriteController::class, 'toggle']);
    Route::get('/favourites/check/{productId}', [FavouriteController::class, 'check']);
    Route::delete('/favourites/{productId}', [FavouriteController::class, 'destroy']);

    // Conversations & Messages (Authenticated Users)
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::get('/conversations/{id}', [ConversationController::class, 'show']);
    Route::get('/conversations/{id}/messages', [ConversationController::class, 'messages']);
    Route::post('/conversations/{id}/read', [ConversationController::class, 'markAsRead']);
    Route::get('/conversations/unread/count', [ConversationController::class, 'unreadCount']);

    // Send messages
    Route::post('/messages', [MessageController::class, 'store']);

    // Rental Availability Management (Owner Only)
    Route::post('/products/{productId}/block-dates', [RentalAvailabilityController::class, 'blockForMaintenance']);

    // Disputes (Authenticated Users)
    Route::get('/disputes', [DisputeController::class, 'index']);
    Route::get('/disputes/{id}', [DisputeController::class, 'show']);
    Route::post('/disputes', [DisputeController::class, 'store']);
    Route::put('/disputes/{id}/status', [DisputeController::class, 'updateStatus']);
    Route::post('/disputes/{id}/resolve', [DisputeController::class, 'resolve']);
});
