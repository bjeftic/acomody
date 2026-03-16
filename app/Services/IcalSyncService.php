<?php

namespace App\Services;

use App\Models\AvailabilityPeriod;
use App\Models\IcalCalendar;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IcalSyncService
{
    public function __construct(private readonly IcalParserService $parser) {}

    public function sync(IcalCalendar $calendar): void
    {
        $icsContent = $this->fetchIcsContent($calendar->ical_url);

        if ($icsContent === null) {
            Log::warning('IcalSyncService: failed to fetch feed', [
                'calendar_id' => $calendar->id,
                'url' => $calendar->ical_url,
            ]);

            return;
        }

        $events = $this->parser->parse($icsContent);

        AvailabilityPeriod::withoutAuthorization(function () use ($calendar, $events): void {
            $this->upsertPeriods($calendar, $events);
            $this->removeStale($calendar, $events);
        });

        IcalCalendar::withoutAuthorization(fn () => $calendar->update(['last_synced_at' => now()]));
    }

    private function fetchIcsContent(string $url): ?string
    {
        try {
            $response = Http::timeout(30)->get($url);

            return $response->successful() ? $response->body() : null;
        } catch (\Throwable $e) {
            Log::error('IcalSyncService: HTTP error', ['url' => $url, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * @param  array<int, array{uid: string, dtstart: Carbon, dtend: Carbon, summary: string}>  $events
     */
    private function upsertPeriods(IcalCalendar $calendar, array $events): void
    {
        foreach ($events as $event) {
            $uid = $event['uid'] ?? null;

            if (! $uid) {
                continue;
            }

            $isAllDay = $event['is_all_day'] ?? false;

            // Airbnb/Booking.com convention: UTC midnight events (e.g. T220000Z for UTC+2)
            // represent local midnight. Convert to local timezone for correct date extraction.
            $localTz = 'Europe/Belgrade';
            $dtstart = $event['dtstart']->copy()->setTimezone($localTz);
            $dtend = $event['dtend']->copy()->setTimezone($localTz);

            // If local time is midnight, null out start/end times (treat as date-only for storage)
            $isLocalMidnight = $dtstart->format('H:i:s') === '00:00:00' && $dtend->format('H:i:s') === '00:00:00';
            $storeDateOnly = $isAllDay || $isLocalMidnight;

            $startDate = $dtstart->copy()->startOfDay();
            $endDate = $dtend->copy()->startOfDay();

            // RFC 5545: DTEND is exclusive only for VALUE=DATE all-day events; subtract 1 day to store inclusive end date
            if ($isAllDay) {
                $endDate->subDay();
            }

            $startTime = $storeDateOnly ? null : $dtstart->format('H:i');
            $endTime = $storeDateOnly ? null : $dtend->format('H:i');

            if ($endDate->lt($startDate)) {
                continue;
            }

            $existing = AvailabilityPeriod::query()
                ->where('ical_calendar_id', $calendar->id)
                ->where('external_uid', $uid)
                ->first();

            if ($existing) {
                $existing->update([
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'notes' => $event['summary'] ?? null,
                ]);
            } else {
                $calendar->accommodation->availabilityPeriods()->create([
                    'status' => 'blocked',
                    'reason' => 'external_booking',
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'notes' => $event['summary'] ?? null,
                    'external_uid' => $uid,
                    'ical_calendar_id' => $calendar->id,
                ]);
            }
        }
    }

    /**
     * Delete periods from this calendar that are no longer in the feed.
     *
     * @param  array<int, array{uid: string, dtstart: Carbon, dtend: Carbon, summary: string, is_all_day: bool}>  $events
     */
    private function removeStale(IcalCalendar $calendar, array $events): void
    {
        $incomingUids = array_filter(array_column($events, 'uid'));

        $query = AvailabilityPeriod::query()->where('ical_calendar_id', $calendar->id);

        if (! empty($incomingUids)) {
            $query->whereNotIn('external_uid', $incomingUids);
        }

        $query->delete();
    }
}
