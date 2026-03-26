<?php

namespace App\Enums\Subscription;

enum PlanCode: string
{
    case Free = 'free';
    case Club = 'club';

    public function label(): string
    {
        return match ($this) {
            self::Free => 'Free',
            self::Club => 'Club',
        };
    }
}
