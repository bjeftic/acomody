<?php

namespace App\Listeners\Booking;

use App\Enums\Accommodation\BookingType;
use App\Events\Booking\BookingCreated;
use App\Mail\Booking\BookingConfirmedMail;
use App\Mail\Booking\BookingRequestedMail;
use App\Notifications\BookingConfirmedNotification;
use App\Notifications\BookingReceivedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBookingCreatedNotifications implements ShouldQueue
{
    public function handle(BookingCreated $event): void
    {
        $booking = $event->booking;

        $booking->load(['accommodation', 'guest', 'host']);

        try {
            if ($booking->booking_type === BookingType::INSTANT_BOOKING->value) {
                Mail::to($booking->guest->email)->queue(new BookingConfirmedMail($booking));
                Mail::to($booking->host->email)->queue(new BookingConfirmedMail($booking, forHost: true));

                $booking->guest->notify(new BookingConfirmedNotification($booking));
            } else {
                Mail::to($booking->guest->email)->queue(new BookingRequestedMail($booking));
                Mail::to($booking->host->email)->queue(new BookingRequestedMail($booking, forHost: true));
            }

            $booking->host->notify(new BookingReceivedNotification($booking));
        } catch (\Throwable $e) {
            Log::error('Booking notification failed', ['booking_id' => $booking->id, 'error' => $e->getMessage()]);
        }
    }
}
