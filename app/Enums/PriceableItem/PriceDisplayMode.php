<?php

namespace App\Enums\PriceableItem;

enum PriceDisplayMode: string
{
    case BASE_ONLY = 'base_only';          // Show only the base price
    case WITH_FEES = 'with_fees';          // Show price + mandatory fees
    case ALL_INCLUSIVE = 'all_inclusive';  // Show total with taxes

    public function label(): string
    {
        return match($this) {
            self::BASE_ONLY => __('enums/priceDisplayMode.base_only'),
            self::WITH_FEES => __('enums/priceDisplayMode.with_fees'),
            self::ALL_INCLUSIVE => __('enums/priceDisplayMode.all_inclusive'),
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
