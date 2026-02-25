<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

/**
 * @property string|null $name
 * @property string $fee_type
 * @property string $charge_type
 * @property string $currency
 * @property string $percentage_rate
 * @property string $amount
 */
class Fee extends Model
{
    use SoftDeletes, HasUlids;

    protected $fillable = [
        'feeable_type',
        'feeable_id',
        'fee_type',
        'name',
        'description',
        'amount',
        'currency',
        'charge_type',
        'percentage_rate',
        'percentage_basis',
        'applies_after_quantity',
        'applies_after_persons',
        'applies_after_amount',
        'mandatory',
        'is_refundable',
        'refund_days',
        'refund_percentage',
        'is_taxable',
        'show_in_breakdown',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage_rate' => 'decimal:2',
        'refund_percentage' => 'decimal:2',
        'applies_after_quantity' => 'integer',
        'applies_after_persons' => 'integer',
        'applies_after_amount' => 'decimal:2',
        'refund_days' => 'integer',
        'display_order' => 'integer',
        'mandatory' => 'boolean',
        'is_refundable' => 'boolean',
        'is_taxable' => 'boolean',
        'show_in_breakdown' => 'boolean',
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

    public function feeable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMandatory($query)
    {
        return $query->where('mandatory', true);
    }

    public function scopeOptional($query)
    {
        return $query->where('mandatory', false);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('fee_type', $type);
    }

    public function scopeTaxable($query)
    {
        return $query->where('is_taxable', true);
    }

    public function scopeForEntity($query, string $entityType, string $entityId)
    {
        return $query->where('feeable_type', $entityType)
                     ->where('feeable_id', $entityId);
    }

    public function scopeOrderByDisplay($query)
    {
        return $query->orderBy('display_order');
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->name) return $this->name;

        return match ($this->fee_type) {
            'cleaning' => 'Cleaning Fee',
            'extra_guest' => 'Extra Guest Fee',
            'pet' => 'Pet Fee',
            'service_charge' => 'Service Charge',
            'booking' => 'Booking Fee',
            default => 'Additional Fee',
        };
    }

    public function getChargeTypeLabelAttribute(): string
    {
        return match ($this->charge_type) {
            'per_unit' => 'Per Unit',
            'per_booking' => 'Per Booking',
            'per_person' => 'Per Person',
            'per_person_per_unit' => 'Per Person Per Unit',
            'percentage' => 'Percentage',
            default => 'Per Booking',
        };
    }

    public function getFormattedAmountAttribute(): string
    {
        if ($this->charge_type === 'percentage') {
            return $this->percentage_rate . '%';
        }

        $symbol = match ($this->currency) {
            'EUR' => '€',
            'USD' => '$',
            'GBP' => '£',
            'RSD' => 'дин',
            default => $this->currency,
        };

        return $symbol . number_format((float) $this->amount, 2);
    }
}
