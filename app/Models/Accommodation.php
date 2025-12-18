<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\Accommodation\AccommodationOccupation;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Accommodation extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'accommodation_draft_id',
        'accommodation_type_id',
        'accommodation_occupation',
        'title',
        'description',
        'booking_type',
        'amenities',
        'house_rules',
        'user_id',
    ];

    protected $casts = [
        'accommodation_occupation' => AccommodationOccupation::class,
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

    public function listing()
    {
        return $this->morphOne(Listing::class, 'listable');
    }

    public function accommodationType()
    {
        return $this->belongsTo(AccommodationType::class);
    }

    /**
     * Get pricing configuration for this accommodation
     */
    public function pricing(): MorphOne
    {
        return $this->morphOne(PriceableItem::class, 'priceable');
    }

    /**
     * Get all pricing periods (seasonal, special dates)
     */
    public function pricingPeriods(): MorphMany
    {
        return $this->morphMany(PricingPeriod::class, 'priceable');
    }

    /**
     * Get active pricing periods only
     */
    public function activePricingPeriods(): MorphMany
    {
        return $this->pricingPeriods()->where('is_active', true);
    }

    /**
     * Get all fees for this accommodation
     */
    public function fees(): MorphMany
    {
        return $this->morphMany(Fee::class, 'feeable');
    }

    /**
     * Get active fees only
     */
    public function activeFees(): MorphMany
    {
        return $this->fees()->where('is_active', true);
    }

    /**
     * Get mandatory fees
     */
    public function mandatoryFees(): MorphMany
    {
        return $this->fees()->where('is_active', true)->where('mandatory', true);
    }

    /**
     * Get optional fees
     */
    public function optionalFees(): MorphMany
    {
        return $this->fees()->where('is_active', true)->where('mandatory', false);
    }

    /**
     * Get all assigned taxes (many-to-many through entity_taxes)
     */
    public function taxes(): MorphToMany
    {
        return $this->morphToMany(TaxRate::class, 'taxable', 'entity_taxes')
            ->withPivot([
                'use_override',
                'override_rate_percent',
                'override_flat_amount',
                'override_included_in_price',
                'override_calculation_basis',
                'is_exempt',
                'exemption_reason',
                'exemption_certificate',
                'exemption_valid_until',
                'custom_rules',
                'is_active'
            ])
            ->withTimestamps();
    }

    /**
     * Get active taxes only
     */
    public function activeTaxes(): MorphToMany
    {
        return $this->taxes()->wherePivot('is_active', true);
    }

    /**
     * Get non-exempt taxes
     */
    public function applicableTaxes(): MorphToMany
    {
        return $this->taxes()
            ->wherePivot('is_active', true)
            ->wherePivot('is_exempt', false);
    }

    // ============================================
    // AVAILABILITY RELATIONSHIPS
    // ============================================

    /**
     * Get all availability periods (blocked/available dates)
     */
    public function availabilityPeriods(): MorphMany
    {
        return $this->morphMany(AvailabilityPeriod::class, 'available');
    }

    /**
     * Get blocked periods only
     */
    public function blockedPeriods(): MorphMany
    {
        return $this->availabilityPeriods()->whereIn('status', ['blocked', 'booked', 'closed']);
    }

    // ============================================
    // PRICING HISTORY
    // ============================================

    /**
     * Get pricing change history
     */
    public function pricingHistory(): MorphMany
    {
        return $this->morphMany(PricingHistory::class, 'priceable');
    }

    // ============================================
    // HELPER METHODS (Simple, no business logic)
    // ============================================

    /**
     * Check if has pricing configured
     */
    public function hasPricing(): bool
    {
        return $this->pricing !== null;
    }

    /**
     * Check if has any fees
     */
    public function hasFees(): bool
    {
        return $this->activeFees()->exists();
    }

    /**
     * Check if has any taxes
     */
    public function hasTaxes(): bool
    {
        return $this->applicableTaxes()->exists();
    }

    /**
     * Check if has availability restrictions
     */
    public function hasAvailabilityRestrictions(): bool
    {
        return $this->blockedPeriods()->exists();
    }

    // ============================================
    // SCOPES
    // ============================================

    public function scopeWithPricing($query)
    {
        return $query->with(['pricing', 'pricingPeriods', 'fees', 'taxes']);
    }

    public function scopeInCountry($query, string $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }

    public function scopeInRegion($query, string $countryCode, string $regionCode)
    {
        return $query->where('country_code', $countryCode)
                     ->where('region_code', $regionCode);
    }

    public function hasValidOccupationType(): bool
    {
        return $this->occupation_type->isValidFor($this->accommodationType);
    }
}
