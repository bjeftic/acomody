<?php

namespace App\Enums\PriceableItem;

enum PricingType: string
{
    case NIGHTLY = 'nightly';        // Per night (accommodations)
    case HOURLY = 'hourly';         // Per hour (services, meeting rooms)
    case DAILY = 'daily';          // Per day (car rentals, equipment)
    case PER_ITEM = 'per_item';       // Per item (menu items, products)
    case PER_PERSON = 'per_person';     // Per person (tours, events)
    case PER_TABLE = 'per_table';      // Per table (restaurant reservations)
    case FIXED = 'fixed';          // Fixed price (services)
    case CUSTOM = 'custom';

    public function label(): string
    {
        return match($this) {
            self::NIGHTLY => __('enums/pricingType.nightly'),
            self::HOURLY => __('enums/pricingType.hourly'),
            self::DAILY => __('enums/pricingType.daily'),
            self::PER_ITEM => __('enums/pricingType.per_item'),
            self::PER_PERSON => __('enums/pricingType.per_person'),
            self::PER_TABLE => __('enums/pricingType.per_table'),
            self::FIXED => __('enums/pricingType.fixed'),
            self::CUSTOM => __('enums/pricingType.custom'),
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
