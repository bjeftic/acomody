<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class EntityTax extends Model
{
    use HasUlids;

    protected $fillable = [
        'taxable_type',
        'taxable_id',
        'tax_rate_id',
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
        'is_active',
    ];

    protected $casts = [
        'use_override' => 'boolean',
        'override_rate_percent' => 'decimal:2',
        'override_flat_amount' => 'decimal:2',
        'override_included_in_price' => 'boolean',
        'is_exempt' => 'boolean',
        'exemption_valid_until' => 'date',
        'custom_rules' => 'array',
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

    public function taxable(): MorphTo
    {
        return $this->morphTo();
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeNotExempt($query)
    {
        return $query->where('is_exempt', false);
    }

    public function scopeForEntity($query, string $entityType, string $entityId)
    {
        return $query->where('taxable_type', $entityType)
                     ->where('taxable_id', $entityId);
    }
}
