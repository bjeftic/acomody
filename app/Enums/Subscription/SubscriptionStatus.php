<?php

namespace App\Enums\Subscription;

enum SubscriptionStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case Trial = 'trial';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Expired => 'Expired',
            self::Cancelled => 'Cancelled',
            self::Trial => 'Trial',
        };
    }

    public function isActive(): bool
    {
        return $this === self::Active || $this === self::Trial;
    }
}
