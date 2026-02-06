<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\ExchangeRate;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Stevebauman\Location\Facades\Location;

class CurrencyService
{
    private const CACHE_KEY_PREFIX = 'exchange_rates';

    /**
     * Get user's currency following Booking.com logic:
     * 1. User's saved preference (if logged in)
     * 2. Session currency (if set by user)
     * 3. Auto-detect from IP (first visit only)
     */
    public static function getUserCurrency()
    {
        $user = Auth::user();

        //1 Authenticated user's preference
        if ($user && $user->preferred_currency) {
            return Currency::where('code', $user->preferred_currency)->firstOrFail();
        }

        //2 Session currency
        if (session()->has('currency')) {
            $sessionCurrency = session()->get('currency');
            return Currency::where('code', $sessionCurrency)->firstOrFail();
        }

        //3 Auto-detect (first visit)
        $detected = self::detectCurrency();

        session()->put('currency', $detected);
        session()->put('currency_auto_detected', true);

        return Currency::where('code', $detected)->firstOrFail();
    }

    /**
     * Detect currency based on user's IP location
     */
    public static function detectCurrency(): string
    {
        $position = Location::get();

        $countryCode = $position?->countryCode;

        if (
            $countryCode &&
            isset(config('constants.country_currency_map')[$countryCode])
        ) {
            return Currency::where('code', config('constants.country_currency_map')[$countryCode])
                ->firstOrFail()
                ->code;
        }

        return Currency::getDefault();
    }

    /**
     * Set user's currency preference (manual change by user)
     */
    public function setUserCurrency(?User $user, string $currencyCode, Request $request): bool
    {
        $currency = Currency::getByCode($currencyCode);

        if (!$currency || !$currency->is_active) {
            return false;
        }

        // Update authenticated user's preference
        if ($user) {
            $user->update(['preferred_currency' => $currency->code]);

            Log::info('User manually changed currency', [
                'user_id' => $user->id,
                'currency' => $currency->code,
                'previous' => $user->getOriginal('preferred_currency')
            ]);
        }

        // Always update session (for both logged in and guest users)
        $request->session()->put('currency', $currency->code);
        $request->session()->put('currency_manually_set', true); // Mark as manually set
        $request->session()->forget('currency_auto_detected'); // Remove auto-detect flag

        Log::info('Currency manually set', [
            'user_id' => $user?->id,
            'currency' => $currency->code,
            'ip' => $request->ip()
        ]);

        return true;
    }

    public static function getAvailableCurrencies(): Collection
    {
        return Currency::all();
    }

    public function getCurrencyByCountry(string $countryCode): ?Currency
    {
        $currencyCode = config('constants.country_currency_map')[$countryCode] ?? 'USD';

        if ($currencyCode) {
            return Currency::getByCode($currencyCode);
        }

        return null;
    }

    /**
     * Format amount in specific currency
     */
    public function format(float $amount, string $currencyCode, bool $includeSymbol = true): string
    {
        $currency = Currency::getByCode($currencyCode);

        if (!$currency) {
            $currency = Currency::getDefault();
        }

        return $currency->format($amount, $includeSymbol);
    }

    /**
     * Convert amount between currencies
     */
    public function convert(
        float $amount,
        string $fromCode,
        string $toCode,
        ?Carbon $date = null
    ): float {
        return ExchangeRate::convert($amount, $fromCode, $toCode, $date);
    }

    /**
     * Convert and format amount
     */
    public function convertAndFormat(
        float $amount,
        string $fromCode,
        string $toCode,
        bool $includeSymbol = true,
        ?Carbon $date = null
    ): string {
        $converted = $this->convert($amount, $fromCode, $toCode, $date);
        return $this->format($converted, $toCode, $includeSymbol);
    }

    /**
     * Get currency information for frontend
     */
    public function getCurrency(string $currencyCode): Currency
    {
        $currency = Currency::getByCode($currencyCode);

        if (!$currency) {
            $currency = Currency::getDefault();
        }

        return $currency;
    }

    /**
     * Get exchange rate history
     */
    public function getRateHistory(
        string $fromCurrency,
        string $toCurrency,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $history = ExchangeRate::getHistory($fromCurrency, $toCurrency, $startDate, $endDate);

        return $history->map(function ($rate) {
            return [
                'date' => $rate->date->format('Y-m-d'),
                'rate' => (float) $rate->rate,
                'formatted_date' => $rate->date->format('M d, Y')
            ];
        })->toArray();
    }

    /**
     * Clear all currency cache
     */
    public function clearCache(): void
    {
        $currencies = Currency::where('is_active', true)->pluck('code');

        foreach ($currencies as $code) {
            Cache::forget(self::CACHE_KEY_PREFIX . '_active_today');
            Cache::forget(self::CACHE_KEY_PREFIX . '_active_' . now()->format('Y-m-d'));
        }
    }
}
