<?php

namespace App\Events\Booking;

use App\Models\Booking;

class BookingCreated extends BookingEvent
{
    public function __construct(public readonly Booking $booking) {}
}
