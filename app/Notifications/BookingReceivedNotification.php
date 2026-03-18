<?php

namespace App\Notifications;

use App\Enums\Notification\NotificationType;
use App\Models\Booking;

class BookingReceivedNotification extends InAppNotification
{
    public function __construct(
        protected Booking $booking,
    ) {}

    public function toData(): array
    {
        return [
            'type' => NotificationType::BookingReceived->value,
            'booking_id' => $this->booking->id,
            'accommodation_id' => $this->booking->accommodation_id,
            'accommodation_title' => $this->booking->accommodation->title,
            'guest_name' => $this->booking->guest->name,
            'check_in' => $this->booking->check_in->toDateString(),
            'check_out' => $this->booking->check_out->toDateString(),
            'is_instant' => $this->booking->booking_type === 'instant_booking',
        ];
    }
}
