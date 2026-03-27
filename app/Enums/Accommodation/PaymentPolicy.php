<?php

namespace App\Enums\Accommodation;

enum PaymentPolicy: string
{
    case IMMEDIATE = 'immediate';
    case TEN_DAYS_BEFORE = 'ten_days_before';
    case ON_SITE = 'on_site';
    case SPLIT = 'split';

    public function label(): string
    {
        return match ($this) {
            self::IMMEDIATE => __('enums/paymentPolicy.immediate'),
            self::TEN_DAYS_BEFORE => __('enums/paymentPolicy.ten_days_before'),
            self::ON_SITE => __('enums/paymentPolicy.on_site'),
            self::SPLIT => __('enums/paymentPolicy.split'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::IMMEDIATE => __('enums/paymentPolicy.immediate_description'),
            self::TEN_DAYS_BEFORE => __('enums/paymentPolicy.ten_days_before_description'),
            self::ON_SITE => __('enums/paymentPolicy.on_site_description'),
            self::SPLIT => __('enums/paymentPolicy.split_description'),
        };
    }

    public static function toArray(): array
    {
        return array_map(fn ($case) => [
            'id' => $case->value,
            'label' => $case->label(),
            'description' => $case->description(),
        ], self::cases());
    }
}
