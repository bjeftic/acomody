<?php

namespace App\Enums\Accommodation;

enum BookingType: string
{
    case INSTANT_BOOKING = 'instant-booking';
    case REQUEST_TO_BOOK = 'request-to-book';

    public function label(): string
    {
        return match($this) {
            self::INSTANT_BOOKING => __('enums/bookingType.instant_booking'),
            self::REQUEST_TO_BOOK => __('enums/bookingType.request_to_book'),
        };
    }

    public function description(): string
    {
        return match($this) {
            self::INSTANT_BOOKING => __('enums/bookingType.instant_booking_description'),
            self::REQUEST_TO_BOOK => __('enums/bookingType.request_to_book_description'),
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
