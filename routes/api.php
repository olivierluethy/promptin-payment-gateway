<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\CheckoutController;
use App\Http\Middleware\HandleUnauthenticated;

// -----------------------------
// Public Routes
// -----------------------------

// Register
Route::post('/register', [AuthController::class, 'register']);

// Login
Route::post('/login', [AuthController::class, 'login']);

// List all products & plans (public)
Route::get('/products', [ProductController::class, 'index']);

// -----------------------------
// Authenticated Routes (Sanctum Token required)
// -----------------------------
Route::middleware(HandleUnauthenticated::class)->group(function () {

    // Get logged-in user info
    Route::get('/me', [AuthController::class, 'me']);

    // Logout (delete token)
    Route::post('/logout', [AuthController::class, 'logout']);

    // List user's subscriptions
    Route::get('/subscriptions', [SubscriptionController::class, 'index']);

    // Subscribe / Checkout with Stripe
    Route::post('/checkout/{planId}', [CheckoutController::class, 'checkout']);
});
// Stripe Webhook (public, keine Auth nötig)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);