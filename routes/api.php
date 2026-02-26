<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\FilterController as PublicFilterController;
use App\Http\Controllers\Public\AccommodationController as PublicAccommodationController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AccommodationController;
use App\Http\Controllers\AccommodationTypeController;
use App\Http\Controllers\AccommodationDraftController;
use App\Http\Controllers\AmenityController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\SearchController;

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

Route::post('/currency/set', [CurrencyController::class, 'set'])
        ->name('api.currency.set');

Route::prefix('public')->name('api.public')->group(function () {
    Route::get('filters', [PublicFilterController::class, 'index'])
        ->name('filters');
    Route::prefix('accommodation')->name('accommodation')->group(function () {
        Route::get('{accommodation}', [PublicAccommodationController::class, 'show'])
            ->name('show');
    });
});

Route::prefix('search')->name('api.search')->group(function () {
    Route::get('locations', [SearchController::class, 'searchLocations'])
        ->name('locations.query');
    Route::get('accommodations', [SearchController::class, 'searchAccommodations'])
        ->name('accommodations.query');
});

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

    Route::put('/users', [UserController::class, 'update'])
        ->name('api.users.update');

    Route::put('/users/password', [UserController::class, 'updatePassword'])
        ->name('api.users.password.update');

    Route::post('/users/avatar', [UserController::class, 'uploadAvatar'])
        ->name('api.users.avatar.upload');

    Route::get('/accommodation-types', [AccommodationTypeController::class, 'index'])
        ->name('api.accommodation.types');

    Route::get('/amenities', [AmenityController::class, 'index'])
        ->name('api.amenities');

    Route::prefix('/fees')->name('api.fees.')->group(function () {
        Route::get('/fee-types', [FeeController::class, 'indexFeeTypes'])
            ->name('fee-types');
        Route::get('/charge-types', [FeeController::class, 'indexFeeChargeTypes'])
            ->name('charge-types');
    });

    Route::prefix('accommodation-drafts')->name('api.accommodation.drafts.')->group(function () {
        Route::get('', [AccommodationDraftController::class, 'index'])
            ->name('accommodation-draft.index');

        Route::get('draft', [AccommodationDraftController::class, 'getAccommodationDraft'])
            ->name('accommodation-draft.get');

        Route::post('', [AccommodationDraftController::class, 'createDraft'])
            ->name('accommodation-draft.create');

        Route::put('{accommodationDraft}', [AccommodationDraftController::class, 'updateDraft'])
            ->name('accommodation-draft.update');

        Route::get('stats', [AccommodationDraftController::class, 'getDraftStats'])
            ->name('accommodation-draft.stats');

        Route::prefix('{accommodationDraft}')->group(function () {
            Route::get('photos', [AccommodationDraftController::class, 'getPhotos'])
                ->name('accommodation-draft.photos.index');

            Route::post('photos', [AccommodationDraftController::class, 'storePhotos'])
                ->name('accommodation-draft.photos.store');

            Route::put('photos/reorder', [AccommodationDraftController::class, 'reorderPhotos'])
                ->name('accommodation-draft.photos.reorder');

            Route::delete('photos', [AccommodationDraftController::class, 'destroyAll'])
                ->name('accommodation-draft.photos.destroy-all');

            Route::prefix('photos/{photo}')->name('accommodation-draft.photos.')->group(function () {
                Route::delete('/', [AccommodationDraftController::class, 'destroyPhoto'])
                    ->name('accommodation-draft.photos.destroy');
            });
        });
    });

    Route::prefix('accommodations')->name('api.accommodations.')->group(function () {
        Route::get('', [AccommodationController::class, 'index'])
            ->name('accommodations.index');
        Route::get('{accommodation}', [AccommodationController::class, 'show'])
            ->name('accommodations.show');
    });
});
