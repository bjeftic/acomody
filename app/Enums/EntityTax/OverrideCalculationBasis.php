<?php

namespace App\Enums\EntityTax;

enum OverrideCalculationBasis: string
{
    case SUBTOTAL_ONLY = 'subtotal_only';
    case SUBTOTAL_AND_FEES = 'subtotal_and_fees';
    case PER_UNIT = 'per_unit';
    case PER_PERSON_PER_UNIT = 'per_person_per_unit';

    public function label(): string
    {
        return match($this) {
            self::SUBTOTAL_ONLY => __('enums/overrideCalculationBasis.subtotal_only'),
            self::SUBTOTAL_AND_FEES => __('enums/overrideCalculationBasis.subtotal_and_fees'),
            self::PER_UNIT => __('enums/overrideCalculationBasis.per_unit'),
            self::PER_PERSON_PER_UNIT => __('enums/overrideCalculationBasis.per_person_per_unit'),
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
