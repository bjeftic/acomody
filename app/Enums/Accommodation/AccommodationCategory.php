<?php

namespace App\Enums\Accommodation;

enum AccommodationCategory: string
{
    case APARTMENT = 'apartment';
    case HOUSE = 'house';
    case HOSPITALITY = 'hospitality';
    case UNIQUE = 'unique';
    case OUTDOOR = 'outdoor';
    case WATER = 'water';

    public function label(): string
    {
        return match ($this) {
            self::APARTMENT => __('enums/accommodation_category.apartment'),
            self::HOUSE => __('enums/accommodation_category.house'),
            self::HOSPITALITY => __('enums/accommodation_category.hospitality'),
            self::UNIQUE => __('enums/accommodation_category.unique'),
            self::OUTDOOR => __('enums/accommodation_category.outdoor'),
            self::WATER => __('enums/accommodation_category.water'),
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::APARTMENT => 'Building',
            self::HOUSE => 'House',
            self::HOSPITALITY => 'Hotel',
            self::UNIQUE => 'Castle',
            self::OUTDOOR => 'Tent',
            self::WATER => 'Sailboat',
        };
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'name' => $this->label(),
            'icon' => $this->icon(),
        ];
    }

    public static function options(): array
    {
        return array_map(
            fn (self $case) => $case->toArray(),
            self::cases()
        );
    }
}
