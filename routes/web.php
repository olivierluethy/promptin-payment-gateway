<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Middleware\StripeWebhook;
use App\Http\Controllers\WebhookController;

Route::get('/success', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');

// Webhook-Route mit stripe.webhook-Middleware
Route::post('/stripe/webhook', [WebhookController::class, 'handle'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);


Route::middleware(['auth'])->group(function () {
    Route::get('/checkout/{planId}', [CheckoutController::class, 'checkout'])->name('checkout.start');
    Route::get('/dashboard', [WebAuthController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
    Route::get('/settings', [WebAuthController::class, 'settings'])->name('settings');
    Route::post('/settings', [WebAuthController::class, 'updateSettings'])->name('settings.update');
    Route::post('/settings/password', [WebAuthController::class, 'updatePassword'])->name('settings.password');
    Route::delete('/settings', [WebAuthController::class, 'deleteAccount'])->name('settings.delete');
    Route::get('/settings/verify-email', [WebAuthController::class, 'verifyEmail'])->name('settings.verifyEmail');
    Route::post('/checkout/{planId}', [WebAuthController::class, 'checkout'])->name('web.checkout');
});

Route::get('/', function () {
    return redirect()->route('login');
});

Route::post('/register', [WebAuthController::class, 'register'])->name('register')->middleware('throttle:10,1');
Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [WebAuthController::class, 'login'])->name('login.post');

Route::get('/reset-password', function (Request $request) {
    $token = $request->query('token');
    $email = $request->query('email');
    if (!$token || !$email) {
        return redirect('/')->with('error', 'Ungültiger oder fehlender Token/E-Mail.');
    }
    return view('auth.reset_password_form', [
        'token' => $token,
        'email' => $email
    ]);
});

Route::post('/reset-password/confirm', [WebAuthController::class, 'resetPasswordConfirm'])->name('password.confirm');
Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'updatePassword'])->name('password.update');

Route::get('/test-mail', function () {
    \Mail::to('business.olivierthomas@gmail.com')->send(new \App\Mail\TestMail());
    return 'Test email sent!';
});