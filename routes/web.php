<?php

use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

// ============================================
// ROBOTS.TXT
// ============================================
Route::get('/robots.txt', function () {
    $content = app()->isProduction()
        ? "User-agent: *\nAllow: /\n\nSitemap: ".url('/sitemap-'.config('app.sitemap_token').'.xml')
        : "User-agent: *\nDisallow: /";

    return response($content, 200)->header('Content-Type', 'text/plain');
});

// ============================================
// SITEMAP (token-protected URL)
// ============================================
Route::get('/sitemap-'.config('app.sitemap_token').'.xml', [SitemapController::class, 'index'])
    ->name('sitemap');

// ============================================
// GOOGLE OAUTH
// ============================================
Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle'])
    ->name('auth.google');

Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])
    ->name('auth.google.callback');

// ============================================
// EMAIL VERIFICATION (Public but throttled)
// ============================================
Route::get('/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
    ->middleware(['throttle:6,1', 'signed'])
    ->name('verification.verify');

// ============================================
// SPA CATCH-ALL ROUTE (must be last)
// ============================================
Route::middleware(['redirect.super.admin'])->group(function () {
    include __DIR__.'/admin.php';
    Route::get('/', function () {
        return view('welcome');
    })->name('login');

    Route::get('/{any}', function () {
        return view('welcome');
    })->where('any', '^(?!admin|api).*$');
});
