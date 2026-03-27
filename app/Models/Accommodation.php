<?php

namespace App\Models;

use App\Enums\Accommodation\AccommodationOccupation;
use App\Enums\Accommodation\AccommodationType;
use App\Enums\Accommodation\PaymentPolicy;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

/**
 * @property PriceableItem|null $pricing
 */
class Accommodation extends Model implements Sitemapable
{
    use HasFactory, HasUlids, Searchable;

    public function toSitemapTag(): Url|string|array
    {
        return Url::create("/accommodations/{$this->id}")
            ->setLastModificationDate($this->updated_at)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            ->setPriority(0.8);
    }

    protected $fillable = [
        'accommodation_draft_id',
        'accommodation_type',
        'accommodation_occupation',
        'title',
        'description',
        'booking_type',
        'max_guests',
        'location_id',
        'longitude',
        'latitude',
        'street_address',
        'is_active',
        'approved_by',
        'views_count',
        'favorites_count',
        'is_featured',
        'check_in_from',
        'check_in_until',
        'check_out_until',
        'quiet_hours_from',
        'quiet_hours_until',
        'cancellation_policy',
        'payment_policy',
        'bedrooms',
        'bathrooms',
        'user_id',
        'ical_token',
        'ical_export_active',
    ];

    protected $casts = [
        'accommodation_occupation' => AccommodationOccupation::class,
        'accommodation_type' => AccommodationType::class,
        'payment_policy' => PaymentPolicy::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (self $accommodation) {
            if (empty($accommodation->ical_token)) {
                do {
                    $token = Str::random(64);
                } while (self::query()->where('ical_token', $token)->exists());

                $accommodation->ical_token = $token;
            }
        });
    }

    public function canBeReadBy($user): bool
    {
        // Public accommodations: approved and active - accessible to everyone (including guests)
        if (! is_null($this->approved_by) && $this->is_active) {
            return true;
        }

        // Owner can always view their own accommodation (even if not approved/active)
        if ($user && $user->id === $this->user_id) {
            return true;
        }

        // Admin who approved it can view
        if ($user && $this->approved_by && $user->id === $this->approved_by) {
            return true;
        }

        // Otherwise, not readable
        return false;
    }

    public function canBeCreatedBy($user): bool
    {
        return $user !== null && $user->is_superadmin;
    }

    public function canBeUpdatedBy($user): bool
    {
        return $user !== null;
    }

    public function canBeDeletedBy($user): bool
    {
        return $user !== null;
    }

    public function isSearchable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        return HostProfile::where('user_id', $this->user_id)
            ->where('is_complete', true)
            ->exists();
    }

    public function typesenseQueryBy(): array
    {
        return ['title'];
    }

    public function toSearchableArray(): array
    {
        $array = [
            'id' => (string) $this->id,
            'title' => (string) $this->title,
            'accommodation_category' => (string) $this->accommodation_type->category()->value,
            'accommodation_occupation' => (string) $this->accommodation_occupation->value,
            'amenities' => $this->amenities()->pluck('slug')->map(fn ($id) => (string) $id)->toArray(),
            'booking_type' => (string) $this->booking_type,
            'cancellation_policy' => (string) $this->cancellation_policy,
            'max_guests' => (int) $this->max_guests,
            'location_id' => (string) $this->location_id,
            'views_count' => (int) $this->views_count,
            'favorites_count' => (int) $this->favorites_count,
            'is_featured' => (bool) $this->is_featured,
            'rating' => (float) 0.0,
            'reviews_count' => (int) 0,
            'regular_price' => (float) ($this->pricing ? $this->pricing->base_price : 0.0),
            'currency' => (string) ($this->pricing ? $this->pricing->currency->code : ''),
            'base_price_eur' => (float) ($this->pricing ? $this->pricing->base_price_eur : 0.0),
            'seasonal_price' => (object) [], // this is also override_price, when this price is set for specific dates
            'bedrooms' => (int) $this->bedrooms,
            'beds' => (int) $this->beds()->sum('quantity'),
            'bathrooms' => (int) $this->bathrooms,
            'photos' => $this->photos
                ->take(5)
                ->pluck('medium_url')
                ->map(fn ($url) => (string) $url)
                ->toArray(),
            'created_at' => $this->created_at ? $this->created_at->timestamp : null,
        ];

        // Add location coordinates ONLY if both latitude and longitude exist
        // This prevents Typesense errors for documents without coordinates
        if ($this->latitude !== null && $this->longitude !== null) {
            $array['location'] = [
                (float) $this->latitude,
                (float) $this->longitude,
            ];
        }

        return $array;
    }

    public function searchableAs(): string
    {
        return 'accommodations';
    }

    /**
     * Get the Typesense collection schema.
     *
     * @return array<string, mixed>
     */
    public function getCollectionSchema(): array
    {
        return [
            'name' => $this->searchableAs(),
            'enable_nested_fields' => true,
            'fields' => [
                [
                    'name' => 'id',
                    'type' => 'string',
                ],
                [
                    'name' => 'title',
                    'type' => 'string',
                    'facet' => false,
                ],
                [
                    'name' => 'location_id',
                    'type' => 'string',
                    'optional' => false,
                ],
                [
                    'name' => 'created_at',
                    'type' => 'int64',
                    'optional' => false,
                ],
                [
                    'name' => 'location',
                    'type' => 'geopoint',
                    'optional' => true,
                ],
                [
                    'name' => 'views_count',
                    'type' => 'int32',
                    'optional' => false,
                ],
                [
                    'name' => 'favorites_count',
                    'type' => 'int32',
                    'optional' => false,
                ],
                [
                    'name' => 'is_featured',
                    'type' => 'bool',
                    'optional' => false,
                ],
                [
                    'name' => 'rating',
                    'type' => 'float',
                    'facet' => true,
                    'optional' => false,
                ],
                [
                    'name' => 'reviews_count',
                    'type' => 'int32',
                    'optional' => false,
                ],
                [
                    'name' => 'accommodation_category',
                    'type' => 'string',
                    'optional' => false,
                    'facet' => true,
                ],
                [
                    'name' => 'accommodation_occupation',
                    'type' => 'string',
                    'optional' => false,
                    'facet' => true,
                ],
                [
                    'name' => 'amenities',
                    'type' => 'string[]',
                    'optional' => false,
                    'facet' => true,
                ],
                [
                    'name' => 'booking_type',
                    'type' => 'string',
                    'optional' => false,
                    'facet' => true,
                ],
                [
                    'name' => 'cancellation_policy',
                    'type' => 'string',
                    'optional' => false,
                    'facet' => true,
                ],
                [
                    'name' => 'max_guests',
                    'type' => 'int32',
                    'optional' => false,
                    'facet' => true,
                ],
                [
                    'name' => 'regular_price',
                    'type' => 'float',
                    'optional' => false,
                    'facet' => true,
                ],
                [
                    'name' => 'currency',
                    'type' => 'string',
                    'optional' => false,
                    'facet' => true,
                ],
                [
                    'name' => 'base_price_eur',
                    'type' => 'float',
                    'optional' => false,
                    'facet' => true,
                ],
                [
                    'name' => 'seasonal_price',
                    'type' => 'object',
                    'optional' => false,
                    'facet' => true,
                ],
                [
                    'name' => 'bedrooms',
                    'type' => 'int32',
                    'optional' => false,
                    'facet' => true,
                ],
                [
                    'name' => 'beds',
                    'type' => 'int32',
                    'optional' => false,
                    'facet' => true,
                ],
                [
                    'name' => 'bathrooms',
                    'type' => 'int32',
                    'optional' => false,
                    'facet' => true,
                ],
                [
                    'name' => 'photos',
                    'type' => 'string[]',
                    'optional' => true,
                    'facet' => false,
                ],
            ],
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function icalCalendars(): HasMany
    {
        return $this->hasMany(IcalCalendar::class);
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'accommodation_amenity');
    }

    public function beds(): HasMany
    {
        return $this->hasMany(AccommodationBed::class);
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'photoable')
            ->orderBy('order');
    }

    public function primaryPhoto(): MorphOne
    {
        return $this->morphOne(Photo::class, 'photoable')
            ->where('is_primary', true);
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
        return $query->with(['pricing', 'pricingPeriods']);
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
}
