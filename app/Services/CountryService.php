<?php

namespace App\Services;

use App\Models\Country;
use Illuminate\Database\Eloquent\Collection;

class CountryService
{
    public static function getAvailableCountries(): Collection
    {
        return Country::all()->sortBy('name');
    }

    public function isCountryActive(string $isoCode2): bool
    {
        $country = Country::where('iso_code_2', $isoCode2)->first();

        return $country ? $country->is_active : false;
    }
}
