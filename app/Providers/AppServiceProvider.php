<?php

namespace App\Providers;

use App\Events\Booking\BookingCancelled;
use App\Events\Booking\BookingConfirmed;
use App\Events\Booking\BookingCreated;
use App\Events\Booking\BookingDeclined;
use App\Listeners\Booking\SendBookingCancelledNotifications;
use App\Listeners\Booking\SendBookingConfirmedNotifications;
use App\Listeners\Booking\SendBookingCreatedNotifications;
use App\Listeners\Booking\SendBookingDeclinedNotification;
use App\Models\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        Event::listen(BookingCreated::class, SendBookingCreatedNotifications::class);
        Event::listen(BookingConfirmed::class, SendBookingConfirmedNotifications::class);
        Event::listen(BookingDeclined::class, SendBookingDeclinedNotification::class);
        Event::listen(BookingCancelled::class, SendBookingCancelledNotifications::class);
    }
}
