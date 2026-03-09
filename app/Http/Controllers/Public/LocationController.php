<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    public function index(): JsonResponse
    {
        $locations = Location::query()
            ->limit(10)
            ->get(['id', 'name'])
            ->map(fn (Location $location) => [
                'id' => $location->id,
                'name' => $location->getTranslation('name', app()->getLocale())
                    ?? $location->getTranslation('name', config('app.fallback_locale')),
            ]);

        return response()->json($locations);
    }
}
