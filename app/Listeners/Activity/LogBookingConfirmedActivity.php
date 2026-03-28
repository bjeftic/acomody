<?php

namespace App\Listeners\Activity;

use App\Enums\Activity\ActivityEvent;
use App\Events\Booking\BookingConfirmed;
use App\Services\ActivityLogService;

class LogBookingConfirmedActivity
{
    public function handle(BookingConfirmed $event): void
    {
        $booking = $event->booking;
        $booking->loadMissing(['accommodation', 'host']);

        $label = $booking->accommodation?->title ?? "#{$booking->id}";

        ActivityLogService::log(
            event: ActivityEvent::BookingConfirmed,
            description: "Booking confirmed for \"{$label}\"",
            subject: $booking,
            causer: $booking->host,
            properties: [
                'accommodation_id' => $booking->accommodation_id,
                'confirmed_at' => $booking->confirmed_at?->toISOString(),
            ],
        );
    }
}
