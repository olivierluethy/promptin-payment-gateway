<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\WebhookController;

// -----------------------------
// Public Routes
// -----------------------------

// Register
Route::post('/register', [AuthController::class, 'register']);

// Login
Route::post('/login', [AuthController::class, 'login']);

// Password Reset (Add this to support password reset functionality)
Route::post('/password-reset', [AuthController::class, 'resetPassword']);

// Refresh Token (Public, as it doesn't require an access token)
Route::post('/refresh', [AuthController::class, 'refresh']);

// List all products & plans (public)
Route::get('/products', [ProductController::class, 'index']);

// -----------------------------
// Authenticated Routes (Sanctum Token required)
// -----------------------------

Route::middleware('auth:sanctum')->group(function () {
    // Get logged-in user info
    Route::get('/me', [AuthController::class, 'me']);

    // Logout (delete token)
    Route::post('/logout', [AuthController::class, 'logout']);

    // Validate Token
    Route::get('/validate-token', [AuthController::class, 'validateToken']);

    // List user's subscriptions
    Route::get('/subscriptions', [SubscriptionController::class, 'index']);

    // Subscribe / Checkout with Stripe
    Route::post('/checkout/{planId}', [CheckoutController::class, 'checkout']);

    // Change Password
    Route::post('/change-password', [AuthController::class, 'changePassword']);
});

// Stripe Webhook (public, no auth needed)
Route::post('/stripe/webhook', [WebhookController::class, 'handle']);
