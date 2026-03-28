<?php

namespace App\Enums\Email;

enum EmailStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Sent => 'Sent',
            self::Failed => 'Failed',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'label-warning',
            self::Sent => 'label-success',
            self::Failed => 'label-danger',
        };
    }
}
