<?php

namespace App\Enums\AvailabilityPeriod;

enum Status: string
{
    case AVAILABLE = 'available';
    case BLOCKED = 'blocked';
    case BOOKED = 'booked';
    case MAINTENANCE = 'maintenance';
    case CLOSED = 'closed';
    case SOLD_OUT = 'sold_out';

    public function label(): string
    {
        return match($this) {
            self::AVAILABLE => __('enums/status.available'),
            self::BLOCKED => __('enums/status.blocked'),
            self::BOOKED => __('enums/status.booked'),
            self::MAINTENANCE => __('enums/status.maintenance'),
            self::CLOSED => __('enums/status.closed'),
            self::SOLD_OUT => __('enums/status.sold_out'),
        };
    }

    public static function toArray(): array
    {
        return array_map(fn($case) => [
            'id' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}
