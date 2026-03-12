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

        $this->upsertPeriods($calendar, $events);
        $this->removeStale($calendar, $events);

        $calendar->update(['last_synced_at' => now()]);
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

            $startDate = $event['dtstart'];
            // iCal DTEND is exclusive for all-day events — store last blocked night
            $endDate = $event['dtend']->copy()->subDay();

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
                    'notes' => $event['summary'] ?? null,
                ]);
            } else {
                $calendar->accommodation->availabilityPeriods()->create([
                    'status' => 'blocked',
                    'reason' => 'external_booking',
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
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
     * @param  array<int, array{uid: string, dtstart: Carbon, dtend: Carbon, summary: string}>  $events
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
