<?php

namespace App\Enums\Accommodation;

enum AccommodationOccupation: string
{
    case ENTIRE_PLACE = 'entire_place';
    case PRIVATE_ROOM = 'private_room';
    case SHARED_ROOM = 'shared_room';

    public function label(): string
    {
        return match($this) {
            self::ENTIRE_PLACE => __('enums/accommodation_occupation.entire_place'),
            self::PRIVATE_ROOM => __('enums/accommodation_occupation.private_room'),
            self::SHARED_ROOM => __('enums/accommodation_occupation.shared_room'),
        };
    }

    public function description(): string
    {
        return match($this) {
            self::ENTIRE_PLACE => __('enums/accommodation_occupation.entire_place_description'),
            self::PRIVATE_ROOM => __('enums/accommodation_occupation.private_room_description'),
            self::SHARED_ROOM => __('enums/accommodation_occupation.shared_room_description'),
        };
    }

    public static function forAccommodationType(AccommodationType $accommodationType): array
    {
        return match($accommodationType) {
            AccommodationType::APARTMENT,
            AccommodationType::HOUSE,
            AccommodationType::VILLA,
            AccommodationType::TOWNHOUSE,
            AccommodationType::CONDO => [
                self::ENTIRE_PLACE,
                self::PRIVATE_ROOM,
                self::SHARED_ROOM
            ],

            AccommodationType::PENTHOUSE,
            AccommodationType::CASTLE,
            AccommodationType::YACHT => [
                self::ENTIRE_PLACE
            ],

            AccommodationType::STUDIO,
            AccommodationType::COTTAGE,
            AccommodationType::CABIN,
            AccommodationType::CHALET,
            AccommodationType::BUNGALOW,
            AccommodationType::LOFT,
            AccommodationType::TINY_HOUSE => [
                self::ENTIRE_PLACE,
                self::PRIVATE_ROOM
            ],

            AccommodationType::HOTEL,
            AccommodationType::RESORT => [
                self::PRIVATE_ROOM
            ],

            AccommodationType::HOSTEL => [
                self::SHARED_ROOM,
                self::PRIVATE_ROOM
            ],

            AccommodationType::GUESTHOUSE,
            AccommodationType::BED_BREAKFAST,
            AccommodationType::GUEST_SUITE => [
                self::ENTIRE_PLACE,
                self::PRIVATE_ROOM,
                self::SHARED_ROOM
            ],

            AccommodationType::FARM_STAY,
            AccommodationType::BARN => [
                self::ENTIRE_PLACE,
                self::PRIVATE_ROOM
            ],

            AccommodationType::BOAT,
            AccommodationType::HOUSEBOAT => [
                self::ENTIRE_PLACE,
                self::PRIVATE_ROOM
            ],

            AccommodationType::CAMPER_RV => [
                self::ENTIRE_PLACE
            ],

            AccommodationType::TENT,
            AccommodationType::GLAMPING,
            AccommodationType::YURT => [
                self::ENTIRE_PLACE,
                self::SHARED_ROOM
            ],

            AccommodationType::TREEHOUSE,
            AccommodationType::DOME,
            AccommodationType::CONTAINER,
            AccommodationType::CAVE,
            AccommodationType::LIGHTHOUSE,
            AccommodationType::WINDMILL,
            AccommodationType::EARTH_HOUSE,
            AccommodationType::SHEPHERD_HOUSE,
            AccommodationType::IGLOO => [
                self::ENTIRE_PLACE
            ],

            AccommodationType::CYCLADIC_HOME,
            AccommodationType::TRULLO,
            AccommodationType::RIAD,
            AccommodationType::RYOKAN => [
                self::ENTIRE_PLACE,
                self::PRIVATE_ROOM
            ],

            default => [
                self::ENTIRE_PLACE
            ],
        };
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'name' => $this->label(),
            'description' => $this->description(),
        ];
    }

    public static function options(): array
    {
        return array_map(
            fn (self $case) => $case->toArray(),
            self::cases()
        );
    }

    public function isValidFor(AccommodationType $accommodationType): bool
    {
        return in_array($this, self::forAccommodationType($accommodationType), true);
    }
}
