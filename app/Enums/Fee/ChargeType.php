<?php

namespace App\Enums\Fee;

enum ChargeType: string
{
    case PER_UNIT = 'per_unit';           // Per night, per hour, per item
    case PER_BOOKING = 'per_booking';        // One-time per booking/reservation
    case PER_PERSON = 'per_person';         // Per person
    case PER_PERSON_PER_UNIT = 'per_person_per_unit'; // Per person per night/hour/item
    case PERCENTAGE = 'percentage';         // Percentage of subtotal

    public function label(): string
    {
        return match($this) {
            self::PER_UNIT => __('enums/chargeType.per_unit'),
            self::PER_BOOKING => __('enums/chargeType.per_booking'),
            self::PER_PERSON => __('enums/chargeType.per_person'),
            self::PER_PERSON_PER_UNIT => __('enums/chargeType.per_person_per_unit'),
            self::PERCENTAGE => __('enums/chargeType.percentage'),
        };
    }

    public function description(): string
    {
        return match($this) {
            self::PER_UNIT => __('enums/chargeType.descriptions.per_unit'),
            self::PER_BOOKING => __('enums/chargeType.descriptions.per_booking'),
            self::PER_PERSON => __('enums/chargeType.descriptions.per_person'),
            self::PER_PERSON_PER_UNIT => __('enums/chargeType.descriptions.per_person_per_unit'),
            self::PERCENTAGE => __('enums/chargeType.descriptions.percentage'),
        };
    }

    public static function toArray(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'name' => $case->label(),
            'description' => $case->description(),
        ], self::cases());
    }
}
