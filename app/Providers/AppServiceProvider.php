<?php

namespace App\Providers;

use App\Enums\Email\EmailStatus;
use App\Events\Booking\BookingCancelled;
use App\Events\Booking\BookingConfirmed;
use App\Events\Booking\BookingCreated;
use App\Events\Booking\BookingDeclined;
use App\Listeners\Booking\SendBookingCancelledNotifications;
use App\Listeners\Booking\SendBookingConfirmedNotifications;
use App\Listeners\Booking\SendBookingCreatedNotifications;
use App\Listeners\Booking\SendBookingDeclinedNotification;
use App\Listeners\Email\LogEmailFailed;
use App\Listeners\Email\LogEmailSending;
use App\Listeners\Email\LogEmailSent;
use App\Models\EmailLog;
use App\Models\Sanctum\PersonalAccessToken;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Queue\Events\JobFailed;
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

        Event::listen(MessageSending::class, LogEmailSending::class);
        Event::listen(MessageSent::class, LogEmailSent::class);

        Event::listen(JobFailed::class, function (JobFailed $event) {
            $logId = $event->job->payload()['email_log_id'] ?? null;

            if ($logId) {
                LogEmailFailed::markFailed((int) $logId, $event->exception->getMessage());

                return;
            }

            // Fallback: mark any pending logs stuck for more than 5 minutes as failed
            EmailLog::where('status', EmailStatus::Pending->value)
                ->where('created_at', '<', now()->subMinutes(5))
                ->update([
                    'status' => EmailStatus::Failed->value,
                    'error_message' => 'Job failed: '.$event->exception->getMessage(),
                ]);
        });
    }
}
