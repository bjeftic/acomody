<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Collection;

class CurrencyService
{
    public static function getAvailableCurrencies(): Collection
    {
        return Currency::all();
    }

    public function isCurrencyActive(string $code): bool
    {
        $currency = Currency::where('code', $code)->first();

        return $currency ? $currency->is_active : false;
    }
}
