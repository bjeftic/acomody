<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/log-in', [AuthenticatedSessionController::class, 'storeWeb'])
    ->name('login.web')
    ->middleware('guest');

Route::get('/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/log-out', [AuthenticatedSessionController::class, 'destroyWeb'])
        ->name('logout.web');
});

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
