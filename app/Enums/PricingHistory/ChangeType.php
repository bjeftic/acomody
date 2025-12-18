<?php

namespace App\Enums\PricingHistory;

enum ChangeType: string
{
    case BASE_PRICE = 'base_price';
    case PERIOD_PRICING = 'period_pricing';
    case FEE = 'fee';
    case TAX = 'tax';
    case DISCOUNT = 'discount';
    case AVAILABILITY = 'availability';
    case BULK_UPDATE = 'bulk_update';

    public function label(): string
    {
        return match($this) {
            self::BASE_PRICE => __('enums/priceChangeType.base_price'),
            self::PERIOD_PRICING => __('enums/priceChangeType.period_pricing'),
            self::FEE => __('enums/priceChangeType.fee'),
            self::TAX => __('enums/priceChangeType.tax'),
            self::DISCOUNT => __('enums/priceChangeType.discount'),
            self::AVAILABILITY => __('enums/priceChangeType.availability'),
            self::BULK_UPDATE => __('enums/priceChangeType.bulk_update'),
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
