<?php

use App\Http\Controllers\SuperAdmin\DashboardController;
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
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
        Route::resource('users', UserController::class)->names('users');
    });
});
