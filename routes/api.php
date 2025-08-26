<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\UserController;

Route::group(['middleware' => ['guest']], function () {
    Route::post('/sign-up', [RegisteredUserController::class, 'signUp'])
        ->name('signup');
    Route::post('/log-in', [AuthenticatedSessionController::class, 'storeApi'])
        ->name('login.api');
    Route::post('/password-reset', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::group(['middleware' => ['web', 'auth:web,sanctum']], function () {
    Route::post('/log-out', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout.api');

    Route::get('/user', [UserController::class, 'show'])
        ->name('user');
});
