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
            ->withCount(['accommodations as active_accommodations_count' => fn ($q) => $q->where('is_active', true)])
            ->whereHas('accommodations', fn ($q) => $q->where('is_active', true))
            ->orderByDesc('active_accommodations_count')
            ->limit(5)
            ->get(['id', 'name'])
            ->map(fn (Location $location) => [
                'id' => $location->id,
                'name' => $location->getTranslation('name', app()->getLocale())
                    ?? $location->getTranslation('name', config('app.fallback_locale')),
            ]);

        if ($locations->isEmpty()) {
            $locations = Location::query()
                ->inRandomOrder()
                ->limit(5)
                ->get(['id', 'name'])
                ->map(fn (Location $location) => [
                    'id' => $location->id,
                    'name' => $location->getTranslation('name', app()->getLocale())
                        ?? $location->getTranslation('name', config('app.fallback_locale')),
                ]);
        }

        return response()->json($locations);
    }
}
