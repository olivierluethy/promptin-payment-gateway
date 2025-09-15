<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\AuthController;

Route::middleware(['auth'])->group(function () {
    Route::get('/checkout/{planId}', [CheckoutController::class, 'checkout'])->name('checkout.start');
    Route::get('/success', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');
});


Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-mail', function () {
    \Mail::to('business.olivierthomas@gmail.com')->send(new \App\Mail\TestMail());
    return 'Test email sent!';
});

// Für Blade-Formular
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
Route::post('/reset-password/confirm', [AuthController::class, 'resetPasswordConfirm']);
