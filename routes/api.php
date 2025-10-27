<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AccommodationTypeController;

Route::group(['middleware' => ['guest']], function () {
    Route::post('/sign-up', [RegisteredUserController::class, 'signUp'])
        ->name('signup');
    Route::post('/log-in', [AuthenticatedSessionController::class, 'store'])
        ->name('login.api');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::group(['middleware' => ['web', 'auth:web,sanctum']], function () {
    Route::post('/log-out', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout.api');

    Route::post('/resend', [VerifyEmailController::class, 'send'])
        ->middleware(['throttle:6,1'])
        ->name('verification.send');

    Route::get('/users', [UserController::class, 'show'])
        ->name('users');

    Route::get('/accommodation-types', [AccommodationTypeController::class, 'index'])
        ->name('accommodation.types');
});
