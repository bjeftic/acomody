<?php

namespace App\Listeners\Activity;

use App\Enums\Activity\ActivityEvent;
use App\Events\Booking\BookingCancelled;
use App\Services\ActivityLogService;

class LogBookingCancelledActivity
{
    public function handle(BookingCancelled $event): void
    {
        $booking = $event->booking;
        $booking->loadMissing(['accommodation', 'cancelledBy']);

        $label = $booking->accommodation?->title ?? "#{$booking->id}";

        ActivityLogService::log(
            event: ActivityEvent::BookingCancelled,
            description: "Booking cancelled for \"{$label}\"",
            subject: $booking,
            causer: $booking->cancelledBy,
            properties: [
                'accommodation_id' => $booking->accommodation_id,
                'cancellation_reason' => $booking->cancellation_reason,
                'refund_amount' => $booking->refund_amount,
                'cancelled_at' => $booking->cancelled_at?->toISOString(),
            ],
        );
    }
}
