<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\RentalController;
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

// Phase 1 - Public API (No Authentication Required)
// Products - Read-only public endpoints
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// Categories - Read-only public endpoints
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);

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
});
