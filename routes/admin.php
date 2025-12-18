<?php

use App\Http\Controllers\SuperAdmin\AccommodationController;
use App\Http\Controllers\SuperAdmin\AccommodationDraftController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\AuthController;
use App\Http\Controllers\SuperAdmin\UserController;
use App\Http\Controllers\SuperAdmin\LocationController;
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

        Route::resource('accommodations', AccommodationController::class)->names('accommodations');

        Route::post('accommodation-drafts/{id}/approve', [AccommodationDraftController::class, 'approve'])->name('accommodation-drafts.approve');
        Route::resource('accommodation-drafts', AccommodationDraftController::class)->names('accommodation-drafts');

        Route::get('locations/search', [LocationController::class, 'search'])->name('locations.search');
        Route::resource('locations', LocationController::class)->names('locations');
    });
});
