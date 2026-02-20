<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Currency extends Model
{
    use HasActiveScope, HasTranslations;

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'is_active',
        'is_default',
        'is_base',
        'decimal_places',
        'symbol_position',
        'thousands_separator',
        'decimal_separator',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'is_base' => 'boolean',
        'decimal_places' => 'integer',
    ];

    public $translatable = ['name'];

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
     * Get users with this preferred currency
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'preferred_currency', 'code');
    }

    /**
     * Get exchange rates to other currencies
     */
    public function exchangeRates(): HasMany
    {
        return $this->hasMany(ExchangeRate::class, 'to_currency', 'code');
    }


    public function priceableItems(): HasMany
    {
        return $this->hasMany(PriceableItem::class);
    }

    /**
     * Get current exchange rate to another currency
     */
    public function getExchangeRateTo(string $toCurrency, ?Carbon $date = null): ?float
    {
        $baseCurrency = static::getBaseCurrency()->code;
        return ExchangeRate::getLatestRate($baseCurrency, $toCurrency, $date);
    }

    /**
     * Get latest exchange rate from base currency
     */
    public function getLatestRate(?Carbon $date = null): ?float
    {
        if ($this->is_base) {
            return 1.0;
        }

        $baseCurrency = static::getBaseCurrency()->code;
        return ExchangeRate::getLatestRate($baseCurrency, $this->code, $date);
    }

    /**
     * Format amount in this currency
     */
    public function format(float $amount, bool $includeSymbol = true): string
    {
        $formatted = number_format(
            $amount,
            $this->decimal_places,
            $this->decimal_separator,
            $this->thousands_separator
        );

        if (!$includeSymbol) {
            return $formatted;
        }

        return $this->symbol_position === 'before'
            ? $this->symbol . ' ' . $formatted
            : $formatted . ' ' . $this->symbol;
    }

    /**
     * Convert amount from base currency to this currency
     */
    public function convertFromBase(float $amount, ?Carbon $date = null): float
    {
        if ($this->is_base) {
            return $amount;
        }

        $rate = $this->getLatestRate($date);

        if (!$rate) {
            return $amount;
        }

        return round($amount * $rate, $this->decimal_places);
    }

    /**
     * Convert amount from this currency to base currency
     */
    public function convertToBase(float $amount, ?Carbon $date = null): float
    {
        if ($this->is_base) {
            return $amount;
        }

        $rate = $this->getLatestRate($date);

        if (!$rate) {
            return $amount;
        }

        return round($amount / $rate, 2);
    }

    /**
     * Get base currency
     */
    public static function getBaseCurrency(): ?self
    {
        return static::where('is_base', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get default currency
     */
    public static function getDefault(): ?self
    {
        return static::where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get active currencies ordered by sort_order
     */
    public static function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get();
    }

    /**
     * Get currency by code
     */
    public static function getByCode(string $code): ?self
    {
        return static::where('code', strtoupper($code))
            ->where('is_active', true)
            ->first();
    }
}
