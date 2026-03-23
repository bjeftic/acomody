<?php

namespace App\Enums\LegalDocument;

enum DocumentType: string
{
    case Terms = 'terms';
    case PrivacyPolicy = 'privacy_policy';

    public function label(): string
    {
        return match ($this) {
            self::Terms => 'Terms & Conditions',
            self::PrivacyPolicy => 'Privacy Policy',
        };
    }

    public function routeSlug(): string
    {
        return match ($this) {
            self::Terms => 'terms',
            self::PrivacyPolicy => 'privacy-policy',
        };
    }
}
