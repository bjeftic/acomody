<?php

namespace App\Console\Commands;

use App\Jobs\SyncIcalCalendarJob;
use App\Models\IcalCalendar;
use Illuminate\Console\Command;

class SyncIcalCalendarsCommand extends Command
{
    protected $signature = 'ical:sync';

    protected $description = 'Dispatch sync jobs for all active external iCal calendars';

    public function handle(): void
    {
        $threshold = now()->subMinutes(5);

        $calendars = IcalCalendar::query()
            ->where('is_active', true)
            ->where(function ($query) use ($threshold) {
                $query->whereNull('last_synced_at')
                    ->orWhere('last_synced_at', '<=', $threshold);
            })
            ->get();

        foreach ($calendars as $calendar) {
            SyncIcalCalendarJob::dispatch($calendar);
        }

        $this->info("Dispatched {$calendars->count()} iCal sync job(s).");
    }
}
