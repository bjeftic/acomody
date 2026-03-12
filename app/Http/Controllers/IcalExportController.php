<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use App\Services\IcalGeneratorService;
use Illuminate\Http\Response;

class IcalExportController extends Controller
{
    public function __construct(private readonly IcalGeneratorService $generatorService) {}

    public function export(string $accommodationId, string $token): Response
    {
        $accommodation = Accommodation::query()
            ->where('id', $accommodationId)
            ->where('ical_token', $token)
            ->where('ical_export_active', true)
            ->firstOrFail();

        $icsContent = $this->generatorService->generate($accommodation);

        return response($icsContent, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="calendar.ics"',
            'Cache-Control' => 'no-cache, must-revalidate',
        ]);
    }
}
