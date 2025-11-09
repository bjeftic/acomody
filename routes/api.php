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
        Route::get('', [AccommodationDraftController::class, 'getDraft'])
            ->name('accommodation-draft.get');

        Route::post('', [AccommodationDraftController::class, 'createDraft'])
            ->name('accommodation-draft.create');

        Route::put('{accommodationDraft}', [AccommodationDraftController::class, 'updateDraft'])
            ->name('accommodation-draft.update');

        Route::get('stats', [AccommodationDraftController::class, 'getDraftStats'])
            ->name('accommodation-draft.stats');

        Route::prefix('{accommodationDraft}')->group(function () {
            Route::get('photos', [AccommodationDraftPhotoController::class, 'index'])
                ->name('accommodation-draft.photos.index');

            Route::post('photos', [AccommodationDraftPhotoController::class, 'store'])
                ->name('accommodation-draft.photos.store');

            Route::put('photos/reorder', [AccommodationDraftPhotoController::class, 'reorder'])
                ->name('accommodation-draft.photos.reorder');

            Route::delete('photos', [AccommodationDraftPhotoController::class, 'destroyAll'])
                ->name('accommodation-draft.photos.destroy-all');

            Route::prefix('photos/{photo}')->name('accommodation-draft.photos.')->group(function () {
                Route::put('/', [AccommodationDraftPhotoController::class, 'update'])
                    ->name('accommodation-draft.photos.update');

                Route::delete('/', [AccommodationDraftPhotoController::class, 'destroy'])
                    ->name('accommodation-draft.photos.destroy');
            });
        });
    });
});
