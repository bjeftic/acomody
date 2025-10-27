<?php

namespace App\Enums;

use App\Models\AccommodationType;

enum AccommodationOccupation: string
{
    case ENTIRE_PLACE = 'entire-place';
    case PRIVATE_ROOM = 'private-room';
    case SHARED_ROOM = 'shared-room';

    public function label(): string
    {
        return match($this) {
            self::ENTIRE_PLACE => __('accommodationOccupation.entire_place'),
            self::PRIVATE_ROOM => __('accommodationOccupation.private_room'),
            self::SHARED_ROOM => __('accommodationOccupation.shared_room'),
        };
    }

    public function description(): string
    {
        return match($this) {
            self::ENTIRE_PLACE => __('accommodationOccupation.entire_place_description'),
            self::PRIVATE_ROOM => __('accommodationOccupation.private_room_description'),
            self::SHARED_ROOM => __('accommodationOccupation.shared_room_description'),
        };
    }

    public static function forAccommodationType(AccommodationType $accommodationType): array
    {
        return match($accommodationType->slug) {
            'apartment', 'house', 'villa' => [
                self::ENTIRE_PLACE,
                self::PRIVATE_ROOM,
                self::SHARED_ROOM
            ],
            'hotel', 'resort' => [
                self::PRIVATE_ROOM
            ],
            'hostel' => [
                self::SHARED_ROOM,
                self::PRIVATE_ROOM
            ],
            'guest-house', 'bnb' => [
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
