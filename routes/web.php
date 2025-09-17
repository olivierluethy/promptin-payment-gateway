<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\PasswordResetController;

// -----------------------------
// Geschützte Routen (Session-basiert)
// -----------------------------
Route::middleware(['auth'])->group(function () {
    Route::get('/checkout/{planId}', [CheckoutController::class, 'checkout'])->name('checkout.start');
    Route::get('/success', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');

    Route::get('/dashboard', [WebAuthController::class, 'dashboard'])->name('dashboard');

    // Logout (POST)
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
});

// -----------------------------
// Öffentliche Routen
// -----------------------------

// Startseite
Route::get('/', function () {
    return redirect()->route('login');
});

// Login (GET: Formular anzeigen, POST: Login absenden)
Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [WebAuthController::class, 'login'])->name('login.post');

// Passwort-Reset – Link aus E-Mail öffnen
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

// Passwort-Reset bestätigen (eigene Implementierung über WebAuthController)
Route::post('/reset-password/confirm', [WebAuthController::class, 'resetPasswordConfirm'])
    ->name('password.confirm');

// -----------------------------
// Passwort vergessen / zurücksetzen
// -----------------------------
Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])
    ->name('password.request');

Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])
    ->name('password.email');

Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])
    ->name('password.reset');

Route::post('/reset-password', [PasswordResetController::class, 'updatePassword'])
    ->name('password.update');

// -----------------------------
// Test-Mail
// -----------------------------
Route::get('/test-mail', function () {
    \Mail::to('business.olivierthomas@gmail.com')->send(new \App\Mail\TestMail());
    return 'Test email sent!';
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [WebAuthController::class, 'dashboard'])->name('dashboard');
    Route::post('/checkout/{planId}', [WebAuthController::class, 'checkout'])->name('web.checkout');
});