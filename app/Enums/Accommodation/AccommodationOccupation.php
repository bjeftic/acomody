<?php

namespace App\Enums\Accommodation;

use App\Models\AccommodationType;

enum AccommodationOccupation: string
{
    case ENTIRE_PLACE = 'entire-place';
    case PRIVATE_ROOM = 'private-room';
    case SHARED_ROOM = 'shared-room';

    public function label(): string
    {
        return match($this) {
            self::ENTIRE_PLACE => __('enums/accommodationOccupation.entire_place'),
            self::PRIVATE_ROOM => __('enums/accommodationOccupation.private_room'),
            self::SHARED_ROOM => __('enums/accommodationOccupation.shared_room'),
        };
    }

    public function description(): string
    {
        return match($this) {
            self::ENTIRE_PLACE => __('enums/accommodationOccupation.entire_place_description'),
            self::PRIVATE_ROOM => __('enums/accommodationOccupation.private_room_description'),
            self::SHARED_ROOM => __('enums/accommodationOccupation.shared_room_description'),
        };
    }

    public static function forAccommodationType(AccommodationType $accommodationType): array
    {
        return match($accommodationType->slug) {
            'apartment', 'house', 'villa', 'townhouse', 'condo' => [
                self::ENTIRE_PLACE,
                self::PRIVATE_ROOM,
                self::SHARED_ROOM
            ],

            'penthouse', 'castle', 'yacht' => [
                self::ENTIRE_PLACE
            ],

            'studio', 'cottage', 'cabin', 'chalet', 'bungalow', 'loft', 'tiny-house' => [
                self::ENTIRE_PLACE,
                self::PRIVATE_ROOM
            ],

            'hotel', 'resort', 'motel', 'pension-inn' => [
                self::PRIVATE_ROOM
            ],

            'hostel' => [
                self::SHARED_ROOM,
                self::PRIVATE_ROOM
            ],

            'guesthouse', 'bed-breakfast', 'homestay', 'guest-suite' => [
                self::ENTIRE_PLACE,
                self::PRIVATE_ROOM,
                self::SHARED_ROOM
            ],

            'farm-stay', 'barn' => [
                self::ENTIRE_PLACE,
                self::PRIVATE_ROOM
            ],

            'boat', 'houseboat' => [
                self::ENTIRE_PLACE,
                self::PRIVATE_ROOM
            ],

            'camper' => [
                self::ENTIRE_PLACE
            ],

            'camping-tent', 'tent', 'glamping', 'yurt' => [
                self::ENTIRE_PLACE,
                self::SHARED_ROOM
            ],

            'treehouse', 'dome', 'container', 'cave', 'lighthouse',
            'windmill', 'earth-house', 'shepherd-hut', 'igloo' => [
                self::ENTIRE_PLACE
            ],

            'cycladic', 'trullo', 'riad', 'ryokan', 'casa' => [
                self::ENTIRE_PLACE,
                self::PRIVATE_ROOM
            ],

            default => [
                self::ENTIRE_PLACE
            ],
        };
    }

    public static function toArray(): array
    {
        return array_map(fn($case) => [
            'id' => $case->value,
            'name' => $case->label(),
            'description' => $case->description(),
        ], self::cases());
    }

    public function isValidFor(AccommodationType $accommodationType): bool
    {
        return in_array($this, self::forAccommodationType($accommodationType), true);
    }
}
