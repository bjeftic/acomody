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
use App\Http\Controllers\AccommodationDraftController;
use App\Http\Controllers\AccommodationDraftPhotoController;
use App\Http\Controllers\AmenityController;

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

    Route::group(['prefix' => 'accommodation-drafts'], function () {
        Route::get('', [AccommodationDraftController::class, 'getDraft'])
            ->name('accommodation.drafts.get');
        Route::post('/save', [AccommodationDraftController::class, 'saveDraft'])
            ->name('accommodation.drafts.save');

        Route::prefix('{accommodationDraft}')->group(function () {

            Route::get('photos', [AccommodationDraftPhotoController::class, 'index'])
                ->name('accommodation-drafts.photos.index');

            Route::post('photos', [AccommodationDraftPhotoController::class, 'store'])
                ->name('accommodation-drafts.photos.store');

            Route::put('photos/reorder', [AccommodationDraftPhotoController::class, 'reorder'])
                ->name('accommodation-drafts.photos.reorder');

            Route::delete('photos', [AccommodationDraftPhotoController::class, 'destroyAll'])
                ->name('accommodation-drafts.photos.destroy-all');

            Route::prefix('photos/{photo}')->group(function () {
                Route::put('/', [AccommodationDraftPhotoController::class, 'update'])
                    ->name('accommodation-drafts.photos.update');

                Route::delete('/', [AccommodationDraftPhotoController::class, 'destroy'])
                    ->name('accommodation-drafts.photos.destroy');
            });
        });
    });

    Route::get('/amenities', [AmenityController::class, 'index'])
        ->name('amenities');
});
