<?php

use App\Http\Controllers\AccommodationController;
use App\Http\Controllers\AccommodationDraftController;
use App\Http\Controllers\AccommodationTypeController;
use App\Http\Controllers\AmenityController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\BedTypeController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\Host\AvailabilityPeriodController as HostAvailabilityPeriodController;
use App\Http\Controllers\Host\BookingController as HostBookingController;
use App\Http\Controllers\Host\DeletionRequestController as HostDeletionRequestController;
use App\Http\Controllers\Host\HostProfileController;
use App\Http\Controllers\Host\IcalCalendarController as HostIcalCalendarController;
use App\Http\Controllers\Host\SubscriptionController;
use App\Http\Controllers\Host\TranslationController;
use App\Http\Controllers\IcalExportController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Public\AccommodationController as PublicAccommodationController;
use App\Http\Controllers\Public\FilterController as PublicFilterController;
use App\Http\Controllers\Public\HomeSectionController as PublicHomeSectionController;
use App\Http\Controllers\Public\LegalDocumentController as PublicLegalDocumentController;
use App\Http\Controllers\Public\LocationController as PublicLocationController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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

Route::post('/language/set', [LanguageController::class, 'set'])
    ->name('api.language.set');

Route::get('plans', [SubscriptionController::class, 'plans'])->name('api.plans');

Route::prefix('public')->name('api.public')->group(function () {
    Route::get('filters', [PublicFilterController::class, 'index'])
        ->name('filters');
    Route::get('locations', [PublicLocationController::class, 'index'])
        ->name('locations.index');
    Route::get('home-sections', [PublicHomeSectionController::class, 'index'])
        ->name('home-sections.index');
    Route::get('legal/{type}', [PublicLegalDocumentController::class, 'show'])
        ->name('legal.show');
    Route::prefix('accommodations')->name('accommodations')->group(function () {
        Route::get('', [PublicAccommodationController::class, 'index'])
            ->name('.index');
        Route::get('{accommodation}', [PublicAccommodationController::class, 'show'])
            ->name('show');
        Route::get('{accommodationId}/blocked-dates', [PublicAccommodationController::class, 'blockedDates'])
            ->name('blocked-dates');
    });
});

// iCal export — public
Route::get('/{accommodationId}/ical/{token}', [IcalExportController::class, 'export'])
    ->name('api.ical.export');

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

    Route::get('/bed-types', [BedTypeController::class, 'index'])
        ->name('api.bed.types');

    Route::get('/amenities', [AmenityController::class, 'index'])
        ->name('api.amenities');

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

        Route::get('{accommodationDraft}', [AccommodationDraftController::class, 'show'])
            ->name('accommodation-draft.show');

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
        Route::patch('{accommodation}', [AccommodationController::class, 'update'])
            ->name('accommodations.update');
        Route::post('{accommodation}/check-availability', [AccommodationController::class, 'checkAvailability'])
            ->name('accommodations.check-availability');
        Route::post('{accommodation}/calculate-price', [AccommodationController::class, 'calculatePrice'])
            ->name('accommodations.calculate-price');
    });

    // Guest bookings
    Route::prefix('bookings')->name('api.bookings.')->group(function () {
        Route::get('', [BookingController::class, 'index'])->name('index');
        Route::post('', [BookingController::class, 'store'])->name('store');
        Route::get('{booking}', [BookingController::class, 'show'])->name('show');
        Route::post('{booking}/cancel', [BookingController::class, 'cancel'])->name('cancel');
    });

    // User account deletion request
    Route::post('users/deletion-request', [UserController::class, 'requestAccountDeletion'])
        ->name('api.users.deletion-request');

    // Host account deletion request
    Route::post('host/deletion-request', [HostDeletionRequestController::class, 'requestHostAccountDeletion'])
        ->name('api.host.deletion-request');

    // Accommodation deletion request
    Route::post('host/accommodations/{accommodation}/deletion-request', [HostDeletionRequestController::class, 'requestAccommodationDeletion'])
        ->name('api.host.accommodations.deletion-request');

    // Host profile
    Route::get('host/subscription', [SubscriptionController::class, 'show'])->name('api.host.subscription');

    Route::prefix('host/profile')->name('api.host.profile.')->group(function () {
        Route::get('', [HostProfileController::class, 'show'])->name('show');
        Route::post('initialize', [HostProfileController::class, 'initialize'])->name('initialize');
        Route::post('', [HostProfileController::class, 'store'])->name('store');
        Route::put('', [HostProfileController::class, 'update'])->name('update');
        Route::post('avatar', [HostProfileController::class, 'uploadAvatar'])->name('avatar');
    });

    // Host iCal calendar management
    Route::prefix('host/accommodations/{accommodation}/ical-calendars')->name('api.host.ical.')->group(function () {
        Route::get('', [HostIcalCalendarController::class, 'index'])->name('index');
        Route::post('', [HostIcalCalendarController::class, 'store'])->name('store');
        Route::put('{icalCalendar}', [HostIcalCalendarController::class, 'update'])->name('update');
        Route::delete('{icalCalendar}', [HostIcalCalendarController::class, 'destroy'])->name('destroy');
    });

    Route::post('host/accommodations/{accommodation}/ical-token/regenerate', [HostIcalCalendarController::class, 'regenerateToken'])
        ->name('api.host.ical.token.regenerate');

    // Host availability periods (blocked/closed dates)
    Route::get('host/blocked-periods', [HostAvailabilityPeriodController::class, 'index'])
        ->name('api.host.blocked-periods.index');

    // Translations (Langbly-powered, rate-limited per user per day)
    Route::post('host/translations/translate', [TranslationController::class, 'translate'])
        ->name('api.host.translations.translate');

    // Notifications
    Route::prefix('notifications')->name('api.notifications.')->group(function () {
        Route::get('', [NotificationController::class, 'index'])->name('index');
        Route::post('{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('read-all', [NotificationController::class, 'markAllRead'])->name('read-all');
    });

    // Host booking management
    Route::prefix('host/bookings')->name('api.host.bookings.')->group(function () {
        Route::get('', [HostBookingController::class, 'index'])->name('index');
        Route::get('{booking}', [HostBookingController::class, 'show'])->name('show');
        Route::post('{booking}/confirm', [HostBookingController::class, 'confirm'])->name('confirm');
        Route::post('{booking}/decline', [HostBookingController::class, 'decline'])->name('decline');
        Route::post('{booking}/cancel', [HostBookingController::class, 'cancel'])->name('cancel');
    });
});
