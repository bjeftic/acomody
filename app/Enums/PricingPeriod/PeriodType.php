<?php

namespace App\Enums\PricingPeriod;

enum PeriodType: string
{
    case SEASONAL = 'seasonal';       // Seasonal pricing (Summer 2025, Winter 2025)
    case SPECIAL_DATE = 'special_date';   // Specific dates (Christmas, New Year's Eve)
    case TIME_OF_DAY = 'time_of_day';    // Different times (Lunch Menu, Dinner Menu)
    case DAY_OF_WEEK = 'day_of_week';    // Different days (Weekend Brunch, Weekday Special)
    case HAPPY_HOUR = 'happy_hour';     // Special time periods (Happy Hour 5-7pm)
    case EVENT = 'event';           // Event-based pricing (Concert, Festival)

    public function label(): string
    {
        return match($this) {
            self::SEASONAL => __('enums/periodType.seasonal'),
            self::SPECIAL_DATE => __('enums/periodType.special_date'),
            self::TIME_OF_DAY => __('enums/periodType.time_of_day'),
            self::DAY_OF_WEEK => __('enums/periodType.day_of_week'),
            self::HAPPY_HOUR => __('enums/periodType.happy_hour'),
            self::EVENT => __('enums/periodType.event'),
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
