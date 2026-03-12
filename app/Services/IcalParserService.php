<?php

namespace App\Services;

use Carbon\Carbon;

class IcalParserService
{
    /**
     * Parse ICS content and return a list of events.
     *
     * @return array<int, array{uid: string, dtstart: Carbon, dtend: Carbon, summary: string}>
     */
    public function parse(string $icsContent): array
    {
        $events = [];
        $currentEvent = null;

        foreach ($this->unfoldLines($icsContent) as $line) {
            $line = trim($line);

            if ($line === 'BEGIN:VEVENT') {
                $currentEvent = [];
            } elseif ($line === 'END:VEVENT') {
                if ($currentEvent !== null && isset($currentEvent['uid'], $currentEvent['dtstart'], $currentEvent['dtend'])) {
                    $events[] = $currentEvent;
                }
                $currentEvent = null;
            } elseif ($currentEvent !== null) {
                [$name, $value] = $this->parseLine($line);

                if ($name === 'UID') {
                    $currentEvent['uid'] = $value;
                } elseif (str_starts_with($name, 'DTSTART')) {
                    $currentEvent['dtstart'] = $this->parseDate($name, $value);
                } elseif (str_starts_with($name, 'DTEND')) {
                    $currentEvent['dtend'] = $this->parseDate($name, $value);
                } elseif ($name === 'SUMMARY') {
                    $currentEvent['summary'] = $this->unescapeText($value);
                }
            }
        }

        return $events;
    }

    /**
     * @return array<int, string>
     */
    private function unfoldLines(string $content): array
    {
        // RFC 5545: unfold lines that start with SPACE or TAB
        $content = str_replace(["\r\n ", "\r\n\t", "\n ", "\n\t"], '', $content);

        return explode("\n", str_replace("\r\n", "\n", $content));
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function parseLine(string $line): array
    {
        $colonPos = strpos($line, ':');

        if ($colonPos === false) {
            return ['', ''];
        }

        return [substr($line, 0, $colonPos), substr($line, $colonPos + 1)];
    }

    private function parseDate(string $name, string $value): Carbon
    {
        $value = trim($value);

        // All-day: DTSTART;VALUE=DATE:20240101
        if (str_contains($name, 'VALUE=DATE')) {
            return Carbon::createFromFormat('Ymd', $value)->startOfDay();
        }

        // Datetime with timezone: DTSTART;TZID=Europe/Paris:20240101T120000
        if (str_contains($name, 'TZID=')) {
            preg_match('/TZID=([^:;]+)/', $name, $matches);
            $tz = $matches[1] ?? config('app.timezone');

            return Carbon::createFromFormat('YmdHis', rtrim($value, 'Z'), $tz)
                ->setTimezone(config('app.timezone'));
        }

        // UTC datetime: DTSTART:20240101T120000Z  (16 chars incl. T and Z)
        if (str_ends_with($value, 'Z') && strlen($value) === 16) {
            return Carbon::createFromFormat('Ymd\THis\Z', $value, 'UTC')
                ->setTimezone(config('app.timezone'));
        }

        // Fallback: treat as date-only
        return Carbon::createFromFormat('Ymd', substr($value, 0, 8))->startOfDay();
    }

    private function unescapeText(string $text): string
    {
        return str_replace(['\\n', '\\;', '\\,', '\\\\'], ["\n", ';', ',', '\\'], $text);
    }
}
