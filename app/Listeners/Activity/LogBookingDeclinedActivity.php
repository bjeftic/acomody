<?php

namespace App\Listeners\Activity;

use App\Enums\Activity\ActivityEvent;
use App\Events\Booking\BookingDeclined;
use App\Services\ActivityLogService;

class LogBookingDeclinedActivity
{
    public function handle(BookingDeclined $event): void
    {
        $booking = $event->booking;
        $booking->loadMissing(['accommodation', 'host']);

        $label = $booking->accommodation?->title ?? "#{$booking->id}";

        ActivityLogService::log(
            event: ActivityEvent::BookingDeclined,
            description: "Booking declined for \"{$label}\"",
            subject: $booking,
            causer: $booking->host,
            properties: [
                'accommodation_id' => $booking->accommodation_id,
                'decline_reason' => $booking->decline_reason,
                'declined_at' => $booking->declined_at?->toISOString(),
            ],
        );
    }
}
