<?php

namespace App\Listeners\Booking;

use App\Events\Booking\BookingDeclined;
use App\Mail\Booking\BookingDeclinedMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBookingDeclinedNotification implements ShouldQueue
{
    public function handle(BookingDeclined $event): void
    {
        $booking = $event->booking;

        $booking->load(['accommodation', 'guest', 'host']);

        try {
            Mail::to($booking->guest->email)->queue(new BookingDeclinedMail($booking));
        } catch (\Throwable $e) {
            Log::error('Booking declined notification failed', ['booking_id' => $booking->id, 'error' => $e->getMessage()]);
        }
    }
}
