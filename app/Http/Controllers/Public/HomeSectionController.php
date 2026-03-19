<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\HomeSection;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Stevebauman\Location\Facades\Location as GeoLocation;

class HomeSectionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $allSections = Cache::rememberForever('home_sections', function () {
            return HomeSection::withoutAuthorization(function () {
                return HomeSection::with(['sectionLocations.location.primaryPhoto', 'sectionLocations.location.country'])
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->get()
                    ->map(fn (HomeSection $section) => $this->serializeSection($section))
                    ->values()
                    ->toArray();
            });
        });

        $countryCode = $this->detectCountryCode($request);

        $filtered = array_values(array_filter(
            $allSections,
            fn (array $section) => $this->isVisibleForCountry($section, $countryCode)
        ));

        return response()->json($filtered);
    }

    private function detectCountryCode(Request $request): ?string
    {
        $ip = $request->ip();

        $position = Cache::remember("geo_ip:{$ip}", now()->addHours(24), fn () => GeoLocation::get($ip) ?: null);

        if (! $position?->countryCode) {
            if (! app()->isProduction()) {
                $testingIp = config('location.testing.ip');
                $position = Cache::remember("geo_ip:{$testingIp}", now()->addHours(24), fn () => GeoLocation::get($testingIp) ?: null);
            }
        }

        return $position?->countryCode ? strtoupper($position->countryCode) : null;
    }

    /**
     * @param  array<string, mixed>  $section
     */
    private function isVisibleForCountry(array $section, ?string $countryCode): bool
    {
        $codes = $section['country_codes'] ?? null;

        // No restriction — visible to everyone
        if (empty($codes)) {
            return true;
        }

        // Country could not be detected — hide country-targeted sections
        if (! $countryCode) {
            return false;
        }

        return in_array($countryCode, $codes, true);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeSection(HomeSection $section): array
    {
        return [
            'id' => $section->id,
            'title' => $section->getTranslations('title'),
            'type' => $section->type->value,
            'sort_order' => $section->sort_order,
            'country_codes' => $section->country_codes,
            'locations' => $this->serializeLocations($section),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function serializeLocations(HomeSection $section): array
    {
        return $section->sectionLocations
            ->map(fn ($sectionLocation) => $this->serializeLocation($sectionLocation->location))
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function serializeLocation(?Location $location): ?array
    {
        if (! $location) {
            return null;
        }

        return [
            'id' => $location->id,
            'name' => $location->getTranslations('name'),
            'photo_url' => $location->primaryPhoto?->medium_url,
            'country_code' => $location->country?->iso_code_2,
        ];
    }
}
