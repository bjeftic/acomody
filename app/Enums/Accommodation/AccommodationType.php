<?php

namespace App\Enums\Accommodation;

enum AccommodationType: string
{
    case APARTMENT = 'apartment';
    case HOUSE = 'house';
    case VILLA = 'villa';
    case CONDO = 'condo';
    case HOTEL = 'hotel';
    case COTTAGE = 'cottage';
    case CABIN = 'cabin';
    case STUDIO = 'studio';
    case BED_BREAKFAST = 'bed_breakfast';
    case TOWNHOUSE = 'townhouse';
    case CHALET = 'chalet';
    case BUNGALOW = 'bungalow';
    case GUESTHOUSE = 'guesthouse';
    case LOFT = 'loft';
    case PENTHOUSE = 'penthouse';
    case RESORT = 'resort';
    case GUEST_SUITE = 'guest_suite';
    case HOSTEL = 'hostel';
    case FARM_STAY = 'farm_stay';
    case BARN = 'barn';
    case TREEHOUSE = 'treehouse';
    case HOUSEBOAT = 'houseboat';
    case BOAT = 'boat';
    case CAMPER_RV = 'camper_rv';
    case TINY_HOUSE = 'tiny_house';
    case GLAMPING = 'glamping';
    case CASTLE = 'castle';
    case YACHT = 'yacht';
    case DOME = 'dome';
    case TENT = 'tent';
    case YURT = 'yurt';
    case CONTAINER = 'container';
    case CAVE = 'cave';
    case LIGHTHOUSE = 'lighthouse';
    case WINDMILL = 'windmill';
    case EARTH_HOUSE = 'earth_house';
    case CYCLADIC_HOME = 'cycladic_home';
    case TRULLO = 'trullo';
    case RIAD = 'riad';
    case RYOKAN = 'ryokan';
    case SHEPHERD_HOUSE = 'shepherd_house';
    case IGLOO = 'igloo';

    public function label(): string
    {
        return match($this) {
            self::APARTMENT => __('enums/accommodation_type.apartment'),
            self::HOUSE => __('enums/accommodation_type.house'),
            self::VILLA => __('enums/accommodation_type.villa'),
            self::CONDO => __('enums/accommodation_type.condo'),
            self::HOTEL => __('enums/accommodation_type.hotel'),
            self::COTTAGE => __('enums/accommodation_type.cottage'),
            self::CABIN => __('enums/accommodation_type.cabin'),
            self::STUDIO => __('enums/accommodation_type.studio'),
            self::BED_BREAKFAST => __('enums/accommodation_type.bed_breakfast'),
            self::TOWNHOUSE => __('enums/accommodation_type.townhouse'),
            self::CHALET => __('enums/accommodation_type.chalet'),
            self::BUNGALOW => __('enums/accommodation_type.bungalow'),
            self::GUESTHOUSE => __('enums/accommodation_type.guesthouse'),
            self::LOFT => __('enums/accommodation_type.loft'),
            self::PENTHOUSE => __('enums/accommodation_type.penthouse'),
            self::RESORT => __('enums/accommodation_type.resort'),
            self::GUEST_SUITE => __('enums/accommodation_type.guest_suite'),
            self::HOSTEL => __('enums/accommodation_type.hostel'),
            self::FARM_STAY => __('enums/accommodation_type.farm_stay'),
            self::BARN => __('enums/accommodation_type.barn'),
            self::TREEHOUSE => __('enums/accommodation_type.treehouse'),
            self::HOUSEBOAT => __('enums/accommodation_type.houseboat'),
            self::BOAT => __('enums/accommodation_type.boat'),
            self::CAMPER_RV => __('enums/accommodation_type.camper_rv'),
            self::TINY_HOUSE => __('enums/accommodation_type.tiny_house'),
            self::GLAMPING => __('enums/accommodation_type.glamping'),
            self::CASTLE => __('enums/accommodation_type.castle'),
            self::YACHT => __('enums/accommodation_type.yacht'),
            self::DOME => __('enums/accommodation_type.dome'),
            self::TENT => __('enums/accommodation_type.tent'),
            self::YURT => __('enums/accommodation_type.yurt'),
            self::CONTAINER => __('enums/accommodation_type.container'),
            self::CAVE => __('enums/accommodation_type.cave'),
            self::LIGHTHOUSE => __('enums/accommodation_type.lighthouse'),
            self::WINDMILL => __('enums/accommodation_type.windmill'),
            self::EARTH_HOUSE => __('enums/accommodation_type.earth_house'),
            self::CYCLADIC_HOME => __('enums/accommodation_type.cycladic_home'),
            self::TRULLO => __('enums/accommodation_type.trullo'),
            self::RIAD => __('enums/accommodation_type.riad'),
            self::RYOKAN => __('enums/accommodation_type.ryokan'),
            self::SHEPHERD_HOUSE => __('enums/accommodation_type.shepherd_house'),
            self::IGLOO => __('enums/accommodation_type.igloo'),
        };
    }

    public function description(): string
    {
        return match($this) {
            self::APARTMENT => __('enums/accommodation_type.apartment_description'),
            self::HOUSE => __('enums/accommodation_type.house_description'),
            self::VILLA => __('enums/accommodation_type.villa_description'),
            self::CONDO => __('enums/accommodation_type.condo_description'),
            self::HOTEL => __('enums/accommodation_type.hotel_description'),
            self::COTTAGE => __('enums/accommodation_type.cottage_description'),
            self::CABIN => __('enums/accommodation_type.cabin_description'),
            self::STUDIO => __('enums/accommodation_type.studio_description'),
            self::BED_BREAKFAST => __('enums/accommodation_type.bed_breakfast_description'),
            self::TOWNHOUSE => __('enums/accommodation_type.townhouse_description'),
            self::CHALET => __('enums/accommodation_type.chalet_description'),
            self::BUNGALOW => __('enums/accommodation_type.bungalow_description'),
            self::GUESTHOUSE => __('enums/accommodation_type.guesthouse_description'),
            self::LOFT => __('enums/accommodation_type.loft_description'),
            self::PENTHOUSE => __('enums/accommodation_type.penthouse_description'),
            self::RESORT => __('enums/accommodation_type.resort_description'),
            self::GUEST_SUITE => __('enums/accommodation_type.guest_suite_description'),
            self::HOSTEL => __('enums/accommodation_type.hostel_description'),
            self::FARM_STAY => __('enums/accommodation_type.farm_stay_description'),
            self::BARN => __('enums/accommodation_type.barn_description'),
            self::TREEHOUSE => __('enums/accommodation_type.treehouse_description'),
            self::HOUSEBOAT => __('enums/accommodation_type.houseboat_description'),
            self::BOAT => __('enums/accommodation_type.boat_description'),
            self::CAMPER_RV => __('enums/accommodation_type.camper_rv_description'),
            self::TINY_HOUSE => __('enums/accommodation_type.tiny_house_description'),
            self::GLAMPING => __('enums/accommodation_type.glamping_description'),
            self::CASTLE => __('enums/accommodation_type.castle_description'),
            self::YACHT => __('enums/accommodation_type.yacht_description'),
            self::DOME => __('enums/accommodation_type.dome_description'),
            self::TENT => __('enums/accommodation_type.tent_description'),
            self::YURT => __('enums/accommodation_type.yurt_description'),
            self::CONTAINER => __('enums/accommodation_type.container_description'),
            self::CAVE => __('enums/accommodation_type.cave_description'),
            self::LIGHTHOUSE => __('enums/accommodation_type.lighthouse_description'),
            self::WINDMILL => __('enums/accommodation_type.windmill_description'),
            self::EARTH_HOUSE => __('enums/accommodation_type.earth_house_description'),
            self::CYCLADIC_HOME => __('enums/accommodation_type.cycladic_home_description'),
            self::TRULLO => __('enums/accommodation_type.trullo_description'),
            self::RIAD => __('enums/accommodation_type.riad_description'),
            self::RYOKAN => __('enums/accommodation_type.ryokan_description'),
            self::SHEPHERD_HOUSE => __('enums/accommodation_type.shepherd_house_description'),
            self::IGLOO => __('enums/accommodation_type.igloo_description'),
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::APARTMENT => 'Apartment',
            self::HOUSE => 'House',
            self::VILLA => 'Villa',
            self::CONDO => 'Condo',
            self::HOTEL => 'Hotel',
            self::COTTAGE => 'Cottage',
            self::CABIN => 'Cabin',
            self::STUDIO => 'Studio',
            self::BED_BREAKFAST => 'BedBreakfast',
            self::TOWNHOUSE => 'Townhouse',
            self::CHALET => 'Chalet',
            self::BUNGALOW => 'Bungalow',
            self::GUESTHOUSE => 'Guesthouse',
            self::LOFT => 'Loft',
            self::PENTHOUSE => 'Penthouse',
            self::RESORT => 'Resort',
            self::GUEST_SUITE => 'Guestsuite',
            self::HOSTEL => 'Apartment',
            self::FARM_STAY => 'Barn',
            self::BARN => 'Barn',
            self::TREEHOUSE => 'Tree',
            self::HOUSEBOAT => 'Boat',
            self::BOAT => 'Boat',
            self::CAMPER_RV => 'Camper',
            self::TINY_HOUSE => 'Tinyhouse',
            self::GLAMPING => 'Glamping',
            self::CASTLE => 'Castle',
            self::YACHT => 'Boat',
            self::DOME => 'Dome',
            self::TENT => 'Tent',
            self::YURT => 'Yurt',
            self::CONTAINER => 'Container',
            self::CAVE => 'Mountain',
            self::LIGHTHOUSE => 'LightHouse',
            self::WINDMILL => 'Windmill',
            self::EARTH_HOUSE => 'EarthHouse',
            self::CYCLADIC_HOME => 'Cycladic',
            self::TRULLO => 'Trullo',
            self::RIAD => 'Riad',
            self::RYOKAN => 'Ryokan',
            self::SHEPHERD_HOUSE => 'ShepherdsHut',
            self::IGLOO => 'Igloo',
        };
    }

    public static function toArray(): array
    {
        return array_map(fn($case) => [
            'id' => $case->value,
            'name' => $case->label(),
            'description' => $case->description(),
            'icon' => $case->icon(),
        ], self::cases());
    }
}
