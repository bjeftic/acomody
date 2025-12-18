<?php

namespace App\Enums\Location;

enum LocationType: string
{
    case STATE = 'state';
    case REGION = 'region';
    case CITY = 'city';
    case NEIGHBORHOOD = 'neighborhood';
    case TOWN = 'town';
    case MOUNTAIN = 'mountain';
    case ISLAND = 'island';

    public function label(): string
    {
        return match($this) {
            self::STATE => __('enums/locationType.state'),
            self::REGION => __('enums/locationType.region'),
            self::CITY => __('enums/locationType.city'),
            self::NEIGHBORHOOD => __('enums/locationType.neighborhood'),
            self::TOWN => __('enums/locationType.town'),
            self::MOUNTAIN => __('enums/locationType.mountain'),
            self::ISLAND => __('enums/locationType.island'),
        };
    }

    public function description(): string
    {
        return match($this) {
            self::STATE => __('enums/locationType.state_description'),
            self::REGION => __('enums/locationType.region_description'),
            self::CITY => __('enums/locationType.city_description'),
            self::NEIGHBORHOOD => __('enums/locationType.neighborhood_description'),
            self::TOWN => __('enums/locationType.town_description'),
            self::MOUNTAIN => __('enums/locationType.mountain_description'),
            self::ISLAND => __('enums/locationType.island_description'),
        };
    }

    public static function toArray(): array
    {
        return array_map(fn($case) => [
            'id' => $case->value,
            'label' => $case->label(),
            'description' => $case->description(),
        ], self::cases());
    }
}
