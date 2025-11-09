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

// ============================================
// PUBLIC ROUTES
// ============================================
Route::post('/sign-up', [RegisteredUserController::class, 'signUp'])
    ->name('api.signup');

Route::post('/log-in', [AuthenticatedSessionController::class, 'store'])
    ->name('api.login');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->name('api.password.email');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->name('api.password.store');

// ============================================
// PROTECTED ROUTES (auth:sanctum)
// ============================================
Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('/log-out', [AuthenticatedSessionController::class, 'destroyApi'])
        ->name('api.logout');

    Route::post('/resend', [VerifyEmailController::class, 'send'])
        ->middleware(['throttle:6,1'])
        ->name('api.verification.send');

    Route::get('/users', [UserController::class, 'show'])
        ->name('api.users');

    Route::get('/accommodation-types', [AccommodationTypeController::class, 'index'])
        ->name('api.accommodation.types');

    Route::get('/amenities', [AmenityController::class, 'index'])
        ->name('api.amenities');

    Route::prefix('accommodation-drafts')->name('api.accommodation.drafts.')->group(function () {
        Route::get('/', [AccommodationDraftController::class, 'getDraft'])
            ->name('get');

        Route::post('/save', [AccommodationDraftController::class, 'saveDraft'])
            ->name('save');

        Route::prefix('{accommodationDraft}')->group(function () {
            Route::get('photos', [AccommodationDraftPhotoController::class, 'index'])
                ->name('photos.index');

            Route::post('photos', [AccommodationDraftPhotoController::class, 'store'])
                ->name('photos.store');

            Route::put('photos/reorder', [AccommodationDraftPhotoController::class, 'reorder'])
                ->name('photos.reorder');

            Route::delete('photos', [AccommodationDraftPhotoController::class, 'destroyAll'])
                ->name('photos.destroy-all');

            Route::prefix('photos/{photo}')->name('photos.')->group(function () {
                Route::put('/', [AccommodationDraftPhotoController::class, 'update'])
                    ->name('update');

                Route::delete('/', [AccommodationDraftPhotoController::class, 'destroy'])
                    ->name('destroy');
            });
        });
    });
});
