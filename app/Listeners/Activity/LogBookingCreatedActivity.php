<?php

namespace App\Listeners\Activity;

use App\Enums\Activity\ActivityEvent;
use App\Events\Booking\BookingCreated;
use App\Services\ActivityLogService;

class LogBookingCreatedActivity
{
    public function handle(BookingCreated $event): void
    {
        $booking = $event->booking;
        $booking->loadMissing(['accommodation', 'guest']);

        $label = $booking->accommodation?->title ?? "#{$booking->id}";

        ActivityLogService::log(
            event: ActivityEvent::BookingCreated,
            description: "Booking created for \"{$label}\" ({$booking->check_in->format('d.m.Y')} – {$booking->check_out->format('d.m.Y')})",
            subject: $booking,
            causer: $booking->guest,
            properties: [
                'accommodation_id' => $booking->accommodation_id,
                'check_in' => $booking->check_in->toDateString(),
                'check_out' => $booking->check_out->toDateString(),
                'guests' => $booking->guests,
                'total_price' => $booking->total_price,
                'currency' => $booking->currency,
                'booking_type' => $booking->booking_type,
            ],
        );
    }
}
