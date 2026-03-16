<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class IcalGeneratorService
{
    private string $dtstamp;

    public function generate(string $accommodationId, string $title): string
    {
        $this->dtstamp = gmdate('Ymd\THis\Z');

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Acomody//Acomody Calendar//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:'.$this->escapeText($title),
        ];

        foreach ($this->buildEvents($accommodationId) as $event) {
            array_push($lines, ...$event);
        }

        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", array_map(fn ($line) => $this->foldLine($line), $lines))."\r\n";
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function buildEvents(string $accommodationId): array
    {
        $events = [];

        $bookings = DB::table('bookings')
            ->where('accommodation_id', $accommodationId)
            ->whereIn('status', ['confirmed', 'pending'])
            ->whereNotNull('check_in')
            ->whereNotNull('check_out')
            ->whereNull('deleted_at')
            ->get(['id', 'check_in', 'check_out']);

        foreach ($bookings as $booking) {
            $events[] = [
                'BEGIN:VEVENT',
                'UID:booking-'.$booking->id.'@acomody.com',
                'DTSTAMP:'.$this->dtstamp,
                'DTSTART;VALUE=DATE:'.date('Ymd', strtotime($booking->check_in)),
                'DTEND;VALUE=DATE:'.date('Ymd', strtotime($booking->check_out)),
                'SUMMARY:Reserved',
                'STATUS:CONFIRMED',
                'TRANSP:OPAQUE',
                'END:VEVENT',
            ];
        }

        $periods = DB::table('availability_periods')
            ->where('available_id', $accommodationId)
            ->where('available_type', 'App\\Models\\Accommodation')
            ->whereIn('status', ['blocked', 'closed'])
            ->whereNull('ical_calendar_id')
            ->get(['id', 'start_date', 'end_date', 'status']);

        foreach ($periods as $period) {
            $events[] = [
                'BEGIN:VEVENT',
                'UID:period-'.$period->id.'@acomody.com',
                'DTSTAMP:'.$this->dtstamp,
                'DTSTART;VALUE=DATE:'.date('Ymd', strtotime($period->start_date)),
                'DTEND;VALUE=DATE:'.date('Ymd', strtotime($period->end_date.' +1 day')),
                'SUMMARY:'.$this->escapeText(ucfirst($period->status)),
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
