<?php

namespace App\Events\Booking;

use App\Models\Booking;

class BookingConfirmed extends BookingEvent
{
    public function __construct(public readonly Booking $booking) {}
}
