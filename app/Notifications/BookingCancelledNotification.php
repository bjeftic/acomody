<?php

namespace App\Notifications;

use App\Enums\Notification\NotificationType;
use App\Models\Booking;

class BookingCancelledNotification extends InAppNotification
{
    public function __construct(
        protected Booking $booking,
    ) {}

    public function toData(): array
    {
        return [
            'type' => NotificationType::BookingCancelled->value,
            'booking_id' => $this->booking->id,
            'accommodation_id' => $this->booking->accommodation_id,
            'accommodation_title' => $this->booking->accommodation->title,
            'check_in' => $this->booking->check_in->toDateString(),
            'check_out' => $this->booking->check_out->toDateString(),
        ];
    }
}
