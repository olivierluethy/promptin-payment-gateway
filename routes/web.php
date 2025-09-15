<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckoutController;

Route::middleware(['auth'])->group(function () {
    Route::get('/checkout/{planId}', [CheckoutController::class, 'checkout'])->name('checkout.start');
    Route::get('/success', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');
});


Route::get('/', function () {
    return view('welcome');
});