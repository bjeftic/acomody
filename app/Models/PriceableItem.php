<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceableItem extends Model
{
    use SoftDeletes, HasUlids;

    protected $fillable = [
        'priceable_type',
        'priceable_id',
        'pricing_type',
        'base_price',
        'currency',
        'base_price_eur',
        'has_weekend_pricing',
        'weekend_price',
        'weekend_days',
        'bulk_discount_percent',
        'bulk_discount_threshold',
        'min_quantity',
        'max_quantity',
        'time_constraints',
        'is_active',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'weekend_price' => 'decimal:2',
        'weekend_days' => 'array',
        'bulk_discount_percent' => 'decimal:2',
        'bulk_discount_threshold' => 'integer',
        'min_quantity' => 'integer',
        'max_quantity' => 'integer',
        'time_constraints' => 'array',
        'has_weekend_pricing' => 'boolean',
        'is_active' => 'boolean',
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
     * Get the owning priceable model (Accommodation, MenuItem, Service, etc.)
     */
    public function priceable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get pricing periods for this item
     */
    public function pricingPeriods(): HasMany
    {
        return $this->hasMany(PricingPeriod::class, 'priceable_id')
            ->where('priceable_type', $this->priceable_type);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('pricing_type', $type);
    }

    public function scopeForEntity($query, string $entityType, string $entityId)
    {
        return $query->where('priceable_type', $entityType)
                     ->where('priceable_id', $entityId);
    }

    public function getFormattedPriceAttribute(): string
    {
        return $this->formatPrice($this->base_price);
    }

    public function getPricingTypeLabelAttribute(): string
    {
        return match ($this->pricing_type) {
            'nightly' => 'Per Night',
            'hourly' => 'Per Hour',
            'daily' => 'Per Day',
            'per_item' => 'Per Item',
            'per_person' => 'Per Person',
            'per_table' => 'Per Table',
            'fixed' => 'Fixed Price',
            default => 'Custom',
        };
    }

    // ============================================
    // HELPER METHODS (Simple utilities only)
    // ============================================

    protected function formatPrice(?float $amount): string
    {
        if ($amount === null) return '';

        $symbol = match ($this->currency) {
            'EUR' => '€',
            'USD' => '$',
            'GBP' => '£',
            'RSD' => 'дин',
            default => $this->currency,
        };

        return $symbol . number_format($amount, 2);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
