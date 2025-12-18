<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

class TaxRate extends Model
{

    protected $fillable = [
        'country_code',
        'region_code',
        'city',
        'tax_name',
        'tax_type',
        'applicable_types',
        'rate_percent',
        'flat_amount',
        'calculation_basis',
        'included_in_price',
        'max_units',
        'min_age',
        'max_age',
        'exempt_below_amount',
        'exempt_categories',
        'effective_from',
        'effective_until',
        'priority',
        'legislation_reference',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'applicable_types' => 'array',
        'rate_percent' => 'decimal:2',
        'flat_amount' => 'decimal:2',
        'included_in_price' => 'boolean',
        'max_units' => 'integer',
        'min_age' => 'integer',
        'max_age' => 'integer',
        'exempt_below_amount' => 'decimal:2',
        'exempt_categories' => 'array',
        'effective_from' => 'date',
        'effective_until' => 'date',
        'priority' => 'integer',
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
     * Polymorphic many-to-many through entity_taxes
     */
    public function accommodations(): BelongsToMany
    {
        return $this->morphedByMany(
            Accommodation::class,
            'taxable',
            'entity_taxes'
        )->withPivot([
            'use_override',
            'override_rate_percent',
            'override_flat_amount',
            'override_included_in_price',
            'is_exempt',
            'exemption_reason',
            'is_active'
        ])->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCountry($query, string $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }

    public function scopeForRegion($query, string $countryCode, ?string $regionCode = null)
    {
        return $query->where('country_code', $countryCode)
            ->where(function ($q) use ($regionCode) {
                $q->whereNull('region_code')
                  ->orWhere('region_code', $regionCode);
            });
    }

    public function scopeForLocation($query, string $countryCode, ?string $regionCode = null, ?string $city = null)
    {
        return $query->where('country_code', $countryCode)
            ->where(function ($q) use ($regionCode, $city) {
                $q->whereNull('region_code')
                  ->orWhere(function ($subQ) use ($regionCode, $city) {
                      $subQ->where('region_code', $regionCode)
                           ->where(function ($cityQ) use ($city) {
                               $cityQ->whereNull('city')
                                    ->orWhere('city', $city);
                           });
                  });
            });
    }

    public function scopeEffectiveOn($query, $date)
    {
        $date = is_string($date) ? Carbon::parse($date) : $date;

        return $query->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_until')
                  ->orWhere('effective_until', '>=', $date);
            });
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('tax_type', $type);
    }

    public function scopeApplicableTo($query, string $entityType)
    {
        return $query->where(function ($q) use ($entityType) {
            $q->whereNull('applicable_types')
              ->orWhereJsonContains('applicable_types', $entityType);
        });
    }

    public function getTaxTypeLabelAttribute(): string
    {
        return match ($this->tax_type) {
            'vat' => 'VAT',
            'sales' => 'Sales Tax',
            'tourist' => 'Tourist Tax',
            'city' => 'City Tax',
            'service' => 'Service Tax',
            'environmental' => 'Environmental Tax',
            'luxury' => 'Luxury Tax',
            default => 'Other',
        };
    }

    public function getLocationDescriptionAttribute(): string
    {
        $parts = [$this->country_code];

        if ($this->region_code) {
            $parts[] = $this->region_code;
        }

        if ($this->city) {
            $parts[] = $this->city;
        }

        return implode(', ', $parts);
    }

    public function getRateDescriptionAttribute(): string
    {
        if ($this->rate_percent) {
            return $this->rate_percent . '%';
        }

        if ($this->flat_amount) {
            return '€' . number_format($this->flat_amount, 2) . ' flat';
        }

        return 'N/A';
    }
}
