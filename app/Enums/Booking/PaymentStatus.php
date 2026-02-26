<?php

namespace App\Enums\Booking;

enum PaymentStatus: string
{
    case UNPAID             = 'unpaid';
    case PAID               = 'paid';
    case REFUNDED           = 'refunded';
    case PARTIALLY_REFUNDED = 'partially_refunded';

    public function label(): string
    {
        return match ($this) {
            self::UNPAID             => 'Unpaid',
            self::PAID               => 'Paid',
            self::REFUNDED           => 'Refunded',
            self::PARTIALLY_REFUNDED => 'Partially Refunded',
        };
    }
}
