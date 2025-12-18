<?php

namespace App\Enums\TaxRate;

enum TaxType: string
{
    case VAT = 'vat';                // Value Added Tax
    case SALES = 'sales';              // Sales Tax
    case TOURIST = 'tourist';            // Tourism/Occupancy Tax
    case CITY = 'city';               // City Tax
    case SERVICE = 'service';            // Service Tax (for restaurants)
    case ENVIRONMENTAL = 'environmental';      // Environmental/Sustainability Tax
    case LUXURY = 'luxury';             // Luxury Tax
    case OTHER = 'other';

    public function label(): string
    {
        return match($this) {
            self::VAT => __('enums/taxType.vat'),
            self::SALES => __('enums/taxType.sales'),
            self::TOURIST => __('enums/taxType.tourist'),
            self::CITY => __('enums/taxType.city'),
            self::SERVICE => __('enums/taxType.service'),
            self::ENVIRONMENTAL => __('enums/taxType.environmental'),
            self::LUXURY => __('enums/taxType.luxury'),
            self::OTHER => __('enums/taxType.other'),
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
