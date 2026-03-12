<?php

namespace App\Services;

use App\Models\Accommodation;

class IcalGeneratorService
{
    public function generate(Accommodation $accommodation): string
    {
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Acomody//Acomody Calendar//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:'.$this->escapeText($accommodation->title),
        ];

        foreach ($this->buildEvents($accommodation) as $event) {
            array_push($lines, ...$event);
        }

        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", array_map(fn ($line) => $this->foldLine($line), $lines))."\r\n";
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function buildEvents(Accommodation $accommodation): array
    {
        $events = [];

        // Confirmed / pending bookings
        $bookings = $accommodation->bookings()
            ->whereIn('status', ['confirmed', 'pending'])
            ->whereNotNull('check_in')
            ->whereNotNull('check_out')
            ->get();

        foreach ($bookings as $booking) {
            $events[] = [
                'BEGIN:VEVENT',
                'UID:booking-'.$booking->id.'@acomody.com',
                'DTSTART;VALUE=DATE:'.$booking->check_in->format('Ymd'),
                'DTEND;VALUE=DATE:'.$booking->check_out->format('Ymd'),
                'SUMMARY:Reserved',
                'STATUS:CONFIRMED',
                'TRANSP:OPAQUE',
                'END:VEVENT',
            ];
        }

        // Manually blocked / closed periods (exclude imported ones to avoid loops)
        $periods = $accommodation->availabilityPeriods()
            ->whereIn('status', ['blocked', 'booked', 'closed'])
            ->whereNull('ical_calendar_id')
            ->get();

        foreach ($periods as $period) {
            $events[] = [
                'BEGIN:VEVENT',
                'UID:period-'.$period->id.'@acomody.com',
                'DTSTART;VALUE=DATE:'.$period->start_date->format('Ymd'),
                // iCal DTEND is exclusive for all-day events
                'DTEND;VALUE=DATE:'.$period->end_date->copy()->addDay()->format('Ymd'),
                'SUMMARY:'.$this->escapeText($period->status_label),
                'STATUS:CONFIRMED',
                'TRANSP:OPAQUE',
                'END:VEVENT',
            ];
        }

        return $events;
    }

    private function escapeText(string $text): string
    {
        return str_replace(
            ['\\', ';', ',', "\n"],
            ['\\\\', '\\;', '\\,', '\\n'],
            $text
        );
    }

    /**
     * RFC 5545: fold lines longer than 75 octets.
     */
    private function foldLine(string $line): string
    {
        if (strlen($line) <= 75) {
            return $line;
        }

        $folded = '';
        while (strlen($line) > 75) {
            $folded .= substr($line, 0, 75)."\r\n ";
            $line = substr($line, 75);
        }

        return $folded.$line;
    }
}
