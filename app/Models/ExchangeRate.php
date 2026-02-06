<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExchangeRate extends Model
{
    protected $fillable = [
        'from_currency',
        'to_currency',
        'rate',
        'date',
        'source',
        'is_active'
    ];

    protected $casts = [
        'rate' => 'decimal:6',
        'date' => 'date',
        'is_active' => 'boolean'
    ];

    public function canBeReadBy($user): bool
    {
        return true;
    }

    public function canBeCreatedBy($user): bool
    {
        return $user !== null;
    }

    public function canBeUpdatedBy($user): bool
    {
        return $user !== null;
    }

    public function canBeDeletedBy($user): bool
    {
        return $user !== null;
    }

    /**
     * Get the from currency
     */
    public function fromCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'from_currency', 'code');
    }

    /**
     * Get the to currency
     */
    public function toCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'to_currency', 'code');
    }

    /**
     * Get latest rate for a currency pair
     */
    public static function getLatestRate(string $fromCurrency, string $toCurrency, ?Carbon $date = null): ?float
    {
        if ($fromCurrency === $toCurrency) {
            return 1.0;
        }

        $date = $date ?? now();

        // First check if exchange rates table is empty
        if (static::count() === 0) {
            throw new \Exception('Exchange rates table is empty.');
        }

        // First try to get direct rate (e.g., EUR -> RSD)
        $rate = static::where('from_currency', $fromCurrency)
            ->where('to_currency', $toCurrency)
            ->where('date', '<=', $date)
            ->where('is_active', true)
            ->orderBy('date', 'desc')
            ->first();

        if ($rate) {
            return (float) $rate->rate;
        }

        // If not found, try to get inverse rate (e.g., RSD -> EUR)
        $inverseRate = static::where('from_currency', $toCurrency)
            ->where('to_currency', $fromCurrency)
            ->where('date', '<=', $date)
            ->where('is_active', true)
            ->orderBy('date', 'desc')
            ->first();

        if ($inverseRate) {
            return 1 / (float) $inverseRate->rate;
        }

        return null;
    }

    /**
     * Get rate for specific date
     */
    public static function getRateForDate(string $fromCurrency, string $toCurrency, Carbon $date): ?float
    {
        $rate = static::where('from_currency', $fromCurrency)
            ->where('to_currency', $toCurrency)
            ->where('date', $date->format('Y-m-d'))
            ->where('is_active', true)
            ->first();

        if ($rate) {
            return (float) $rate->rate;
        }

        // Fallback to latest available rate before this date
        return static::getLatestRate($fromCurrency, $toCurrency, $date);
    }

    /**
     * Get all rates for a specific date
     */
    public static function getRatesForDate(string $baseCurrency, Carbon $date): array
    {
        $rates = static::where('from_currency', $baseCurrency)
            ->where('date', '<=', $date)
            ->where('is_active', true)
            ->select('to_currency', 'rate', 'date')
            ->get()
            ->groupBy('to_currency')
            ->map(function ($group) {
                return $group->sortByDesc('date')->first();
            })
            ->pluck('rate', 'to_currency')
            ->toArray();

        // Add base currency rate (always 1.0)
        $rates[$baseCurrency] = 1.0;

        return $rates;
    }

    /**
     * Store or update rate for a date
     */
    public static function setRate(
        string $fromCurrency,
        string $toCurrency,
        float $rate,
        Carbon $date,
        ?string $source = null
    ): self {
        return static::updateOrCreate(
            [
                'from_currency' => $fromCurrency,
                'to_currency' => $toCurrency,
                'date' => $date->format('Y-m-d'),
                'source' => $source ?? 'manual'
            ],
            [
                'rate' => $rate,
                'is_active' => true
            ]
        );
    }

    /**
     * Convert amount between currencies
     */
    public static function convert(
        float $amount,
        string $fromCurrency,
        string $toCurrency,
        ?Carbon $date = null
    ): float {
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        $date = $date ?? now();

        // Get base currency (EUR)
        $baseCurrency = Currency::where('is_base', true)->first()?->code ?? 'EUR';

        // If converting from base currency
        if ($fromCurrency === $baseCurrency) {
            $rate = static::getLatestRate($baseCurrency, $toCurrency, $date);
            return $rate ? $amount * $rate : $amount;
        }

        // If converting to base currency
        if ($toCurrency === $baseCurrency) {
            $rate = static::getLatestRate($baseCurrency, $fromCurrency, $date);
            return $rate ? $amount / $rate : $amount;
        }

        // Converting between two non-base currencies (via base currency)
        $fromRate = static::getLatestRate($baseCurrency, $fromCurrency, $date);
        $toRate = static::getLatestRate($baseCurrency, $toCurrency, $date);

        if (!$fromRate || !$toRate) {
            return $amount;
        }

        // Convert to base, then to target
        $baseAmount = $amount / $fromRate;
        return $baseAmount * $toRate;
    }

    /**
     * Get historical rates for a currency pair
     */
    public static function getHistory(
        string $fromCurrency,
        string $toCurrency,
        Carbon $startDate,
        Carbon $endDate
    ): \Illuminate\Database\Eloquent\Collection {
        return static::where('from_currency', $fromCurrency)
            ->where('to_currency', $toCurrency)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('is_active', true)
            ->orderBy('date', 'asc')
            ->get();
    }

    /**
     * Get average rate for a period
     */
    public static function getAverageRate(
        string $fromCurrency,
        string $toCurrency,
        Carbon $startDate,
        Carbon $endDate
    ): ?float {
        $average = static::where('from_currency', $fromCurrency)
            ->where('to_currency', $toCurrency)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('is_active', true)
            ->avg('rate');

        return $average ? (float) $average : null;
    }

    /**
     * Deactivate old rates (optional cleanup)
     */
    public static function deactivateOldRates(int $daysToKeep = 365): int
    {
        $cutoffDate = now()->subDays($daysToKeep);

        return static::where('date', '<', $cutoffDate)
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }

    /**
     * Get latest update timestamp for a currency
     */
    public static function getLastUpdateDate(string $currency): ?Carbon
    {
        $rate = static::where('to_currency', $currency)
            ->where('is_active', true)
            ->orderBy('date', 'desc')
            ->first();

        return $rate?->date;
    }
}
