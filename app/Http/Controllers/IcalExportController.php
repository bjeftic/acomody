<?php

namespace App\Http\Controllers;

use App\Services\IcalGeneratorService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IcalExportController extends Controller
{
    public function __construct(private readonly IcalGeneratorService $generatorService) {}

    public function export(string $accommodationId, string $token): Response
    {
        $accommodation = DB::table('accommodations')
            ->where('id', $accommodationId)
            ->where('ical_token', $token)
            ->where('ical_export_active', true)
            ->first();

        if (! $accommodation) {
            throw new NotFoundHttpException;
        }

        $icsContent = $this->generatorService->generate($accommodationId, $accommodation->title);

        return response($icsContent, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="calendar.ics"',
            'Cache-Control' => 'no-cache, must-revalidate',
        ]);
    }
}
