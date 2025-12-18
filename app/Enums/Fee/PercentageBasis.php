<?php

namespace App\Enums\Fee;

enum PercentageBasis: string
{
    case SUBTOTAL = 'subtotal';           // Percentage of subtotal only
    case SUBTOTAL_AND_FEES = 'subtotal_and_fees';  // Percentage of subtotal + other fees
    case TOTAL = 'total';               // Percentage of everything

    public function label(): string
    {
        return match($this) {
            self::SUBTOTAL => __('enums/percentageBasis.subtotal'),
            self::SUBTOTAL_AND_FEES => __('enums/percentageBasis.subtotal_and_fees'),
            self::TOTAL => __('enums/percentageBasis.total'),
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
