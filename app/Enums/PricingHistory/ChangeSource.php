<?php

namespace App\Enums\PricingHistory;

enum ChangeSource: string
{
    case MANUAL = 'manual';
    case API = 'api';
    case BULK_IMPORT = 'bulk_import';
    case AUTOMATION = 'automation';
    case EXTERNAL_SYNC = 'external_sync';
    case SYSTEM = 'system';

    public function label(): string
    {
        return match($this) {
            self::MANUAL => __('enums/changeSource.manual'),
            self::API => __('enums/changeSource.api'),
            self::BULK_IMPORT => __('enums/changeSource.bulk_import'),
            self::AUTOMATION => __('enums/changeSource.automation'),
            self::EXTERNAL_SYNC => __('enums/changeSource.external_sync'),
            self::SYSTEM => __('enums/changeSource.system'),
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
