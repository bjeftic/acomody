<?php

namespace App\Enums\Booking;

enum BookingStatus: string
{
    case PENDING   = 'pending';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';
    case DECLINED  = 'declined';
    case COMPLETED = 'completed';
    case NO_SHOW   = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::PENDING   => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::CANCELLED => 'Cancelled',
            self::DECLINED  => 'Declined',
            self::COMPLETED => 'Completed',
            self::NO_SHOW   => 'No Show',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::PENDING, self::CONFIRMED]);
    }
}
