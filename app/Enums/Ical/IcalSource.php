<?php

namespace App\Enums\Ical;

enum IcalSource: string
{
    case Airbnb = 'airbnb';
    case Booking = 'booking';
    case Expedia = 'expedia';
    case Vrbo = 'vrbo';
    case Agoda = 'agoda';
    case TripCom = 'trip_com';
    case Tripadvisor = 'tripadvisor';
    case HomeAway = 'homeaway';
    case Google = 'google_calendar';
    case Apple = 'apple_calendar';
    case Outlook = 'outlook';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Airbnb => 'Airbnb',
            self::Booking => 'Booking.com',
            self::Expedia => 'Expedia',
            self::Vrbo => 'Vrbo',
            self::Agoda => 'Agoda',
            self::TripCom => 'Trip.com',
            self::Tripadvisor => 'Tripadvisor',
            self::HomeAway => 'HomeAway',
            self::Google => 'Google Calendar',
            self::Apple => 'Apple Calendar',
            self::Outlook => 'Microsoft Outlook',
            self::Other => 'Other',
        };
    }
}
