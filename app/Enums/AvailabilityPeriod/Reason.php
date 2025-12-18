<?php

namespace App\Enums\AvailabilityPeriod;

enum Reason: string
{
    case OWNER_BLOCKED = 'owner_blocked';
    case MAINTENANCE = 'maintenance';
    case BOOKING = 'booking';
    case EXTERNAL_BOOKING = 'external_booking';
    case HOLIDAY = 'holiday';
    case CLOSED_DAY = 'closed_day';
    case CAPACITY_REACHED = 'capacity_reached';
    case WEATHER = 'weather';
    case EVENT = 'event';
    case OTHER = 'other';

    public function label(): string
    {
        return match($this) {
            self::OWNER_BLOCKED => __('enums/reason.owner_blocked'),
            self::MAINTENANCE => __('enums/reason.maintenance'),
            self::BOOKING => __('enums/reason.booking'),
            self::EXTERNAL_BOOKING => __('enums/reason.external_booking'),
            self::HOLIDAY => __('enums/reason.holiday'),
            self::CLOSED_DAY => __('enums/reason.closed_day'),
            self::CAPACITY_REACHED => __('enums/reason.capacity_reached'),
            self::WEATHER => __('enums/reason.weather'),
            self::EVENT => __('enums/reason.event'),
            self::OTHER => __('enums/reason.other'),
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
