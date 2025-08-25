<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\UserController;

Route::post('/sign-up', [RegisteredUserController::class, 'signUp'])
    ->name('signup')
    ->middleware('guest'); // Ensure this route is accessible only to guests
Route::post('/log-in', [AuthenticatedSessionController::class, 'storeApi'])
    ->name('login.api')
    ->middleware('guest'); // Ensure this route is accessible only to guests


Route::group(['middleware' => ['web', 'auth:web,sanctum']], function () {
    Route::post('/log-out', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout.api');

    Route::get('/user', [UserController::class, 'show'])
        ->name('user');
});
