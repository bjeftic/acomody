<?php

namespace App\Enums\Notification;

enum NotificationType: string
{
    case AccommodationUnderReview = 'accommodation_under_review';
    case AccommodationApproved = 'accommodation_approved';
    case BookingReceived = 'booking_received';
    case BookingConfirmed = 'booking_confirmed';
    case BookingCancelled = 'booking_cancelled';

    public function label(): string
    {
        return match ($this) {
            self::AccommodationUnderReview => 'Accommodation Under Review',
            self::AccommodationApproved => 'Accommodation Approved',
            self::BookingReceived => 'New Booking',
            self::BookingConfirmed => 'Booking Confirmed',
            self::BookingCancelled => 'Booking Cancelled',
        };
    }
}
