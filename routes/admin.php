<?php

use App\Http\Controllers\SuperAdmin\AccommodationController;
use App\Http\Controllers\SuperAdmin\AccommodationDraftController;
use App\Http\Controllers\SuperAdmin\AuthController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\DeletionRequestController;
use App\Http\Controllers\SuperAdmin\FeatureFlagController;
use App\Http\Controllers\SuperAdmin\HomeSectionController;
use App\Http\Controllers\SuperAdmin\LegalDocumentController;
use App\Http\Controllers\SuperAdmin\LocationController;
use App\Http\Controllers\SuperAdmin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes (Blade Panel - Super Admins Only)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'super.admin'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

        Route::resource('users', UserController::class)->names('users');

        Route::resource('accommodations', AccommodationController::class)->only(['index', 'show'])->names('accommodations');

        Route::post('accommodation-drafts/{id}/approve', [AccommodationDraftController::class, 'approve'])->name('accommodation-drafts.approve');
        Route::post('accommodation-drafts/{id}/reject', [AccommodationDraftController::class, 'reject'])->name('accommodation-drafts.reject');
        Route::post('accommodation-drafts/{id}/comments', [AccommodationDraftController::class, 'addComment'])->name('accommodation-drafts.comments.store');
        Route::resource('accommodation-drafts', AccommodationDraftController::class)->only(['index', 'show'])->names('accommodation-drafts');

        Route::get('locations/search', [LocationController::class, 'search'])->name('locations.search');
        Route::resource('locations', LocationController::class)->names('locations');

        Route::get('home-sections/search-locations', [HomeSectionController::class, 'searchLocations'])->name('home-sections.search-locations');
        Route::post('home-sections/{homeSection}/locations', [HomeSectionController::class, 'addLocation'])->name('home-sections.locations.store');
        Route::delete('home-sections/{homeSection}/locations/{sectionLocation}', [HomeSectionController::class, 'removeLocation'])->name('home-sections.locations.destroy');
        Route::resource('home-sections', HomeSectionController::class)->names('home-sections');

        Route::get('deletion-requests', [DeletionRequestController::class, 'index'])->name('deletion-requests.index');
        Route::post('deletion-requests/{id}/approve', [DeletionRequestController::class, 'approve'])->name('deletion-requests.approve');
        Route::post('deletion-requests/{id}/reject', [DeletionRequestController::class, 'reject'])->name('deletion-requests.reject');

        Route::post('legal-documents/{legalDocument}/publish', [LegalDocumentController::class, 'publish'])->name('legal-documents.publish');
        Route::resource('legal-documents', LegalDocumentController::class)->only(['index', 'create', 'store', 'show', 'destroy'])->names('legal-documents');

        Route::post('feature-flags/{featureFlag}/toggle', [FeatureFlagController::class, 'toggle'])->name('feature-flags.toggle');
        Route::resource('feature-flags', FeatureFlagController::class)->except(['show'])->names('feature-flags');
    });
});
