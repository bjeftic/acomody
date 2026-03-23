<?php

namespace App\Enums\LegalDocument;

enum SectionType: string
{
    case Heading = 'heading';
    case Subheading = 'subheading';
    case Paragraph = 'paragraph';

    public function label(): string
    {
        return match ($this) {
            self::Heading => 'Heading (H2)',
            self::Subheading => 'Subheading (H3)',
            self::Paragraph => 'Paragraph',
        };
    }
}
