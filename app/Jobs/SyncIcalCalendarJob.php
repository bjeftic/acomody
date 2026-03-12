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

    public function __construct(public readonly IcalCalendar $calendar) {}

    public function handle(IcalSyncService $syncService): void
    {
        $syncService->sync($this->calendar);
    }
}
