<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\VerifyEmailController;

// ============================================
// EMAIL VERIFICATION (Public but throttled)
// ============================================
Route::get('/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
    ->middleware(['throttle:6,1', 'signed'])
    ->name('verification.verify');

// ============================================
// SPA CATCH-ALL ROUTE (must be last)
// ============================================
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
