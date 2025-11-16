<?php

use App\Http\Controllers\SuperAdmin\AccommodationController;
use App\Http\Controllers\SuperAdmin\AccommodationDraftController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\AuthController;
use App\Http\Controllers\SuperAdmin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes (Blade Panel - Super Admins Only)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'super.admin'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
        Route::resource('users', UserController::class)->names('users');
        Route::resource('accommodations', AccommodationController::class)->names('accommodations');
        Route::resource('accommodation-drafts', AccommodationDraftController::class)->names('accommodation-drafts');
    });
});
