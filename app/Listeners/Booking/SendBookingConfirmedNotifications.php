<?php

namespace App\Listeners\Booking;

use App\Events\Booking\BookingConfirmed;
use App\Mail\Booking\BookingConfirmedMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBookingConfirmedNotifications implements ShouldQueue
{
    public function handle(BookingConfirmed $event): void
    {
        $booking = $event->booking;

        $booking->load(['accommodation', 'guest', 'host']);

        try {
            Mail::to($booking->guest->email)->queue(new BookingConfirmedMail($booking));
            Mail::to($booking->host->email)->queue(new BookingConfirmedMail($booking, forHost: true));
        } catch (\Throwable $e) {
            Log::error('Booking confirmed notification failed', ['booking_id' => $booking->id, 'error' => $e->getMessage()]);
        }
    }
}
