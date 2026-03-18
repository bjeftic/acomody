<?php

namespace App\Jobs;

use App\Models\IcalCalendar;
use App\Services\IcalSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncIcalCalendarJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(public readonly string $calendarId) {}

    public function handle(IcalSyncService $syncService): void
    {
        $calendar = IcalCalendar::withoutAuthorization(
            fn () => IcalCalendar::query()->findOrFail($this->calendarId)
        );

        $syncService->sync($calendar);
    }
}
