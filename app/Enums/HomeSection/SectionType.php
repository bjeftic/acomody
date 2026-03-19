<?php

namespace App\Enums\HomeSection;

enum SectionType: string
{
    case Locations = 'locations';
    case Accommodations = 'accommodations';

    public function label(): string
    {
        return match ($this) {
            SectionType::Locations => 'Location Cards',
            SectionType::Accommodations => 'Accommodations by Location',
        };
    }
}
