<?php

namespace App\Events\Booking;

use App\Models\Booking;

class BookingCancelled extends BookingEvent
{
    public function __construct(
        public readonly Booking $booking,
        public readonly int $cancelledByUserId,
    ) {}
}
