<?php

namespace App\Listeners\Booking;

use App\Events\Booking\BookingCancelled;
use App\Mail\Booking\BookingCancelledMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBookingCancelledNotifications implements ShouldQueue
{
    public function handle(BookingCancelled $event): void
    {
        $booking = $event->booking;

        User::withoutAuthorization(fn () => $booking->load(['accommodation', 'guest', 'host']));

        try {
            Mail::to($booking->guest->email)->queue(new BookingCancelledMail($booking));

            if ($event->cancelledByUserId !== $booking->host_user_id) {
                Mail::to($booking->host->email)->queue(new BookingCancelledMail($booking, forHost: true));
            }
        } catch (\Throwable $e) {
            Log::error('Booking cancellation notification failed', ['booking_id' => $booking->id, 'error' => $e->getMessage()]);
        }
    }
}
