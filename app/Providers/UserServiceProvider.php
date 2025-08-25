<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\UserService;

class UserServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(UserService::class);
    }

    public function boot(): void
    {
        // Any bootstrapping for user service
    }
}
