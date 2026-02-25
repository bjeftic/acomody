<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

/**
 * @property string $change_type
 * @property string $change_source
 */
class PricingHistory extends Model
{
    use HasUlids;

    protected $fillable = [
        'priceable_type',
        'priceable_id',
        'change_type',
        'field_name',
        'old_values',
        'new_values',
        'change_reason',
        'change_source',
        'changed_by_user_id',
        'changed_by_system',
        'changed_from_ip',
        'changed_at',
        'can_rollback',
        'rolled_back_at',
        'rolled_back_by_user_id',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'changed_at' => 'datetime',
        'can_rollback' => 'boolean',
        'rolled_back_at' => 'datetime',
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

    public function priceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }

    public function rolledBackByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rolled_back_by_user_id');
    }

    public function scopeForEntity($query, string $entityType, string $entityId)
    {
        return $query->where('priceable_type', $entityType)
                     ->where('priceable_id', $entityId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('change_type', $type);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('changed_by_user_id', $userId);
    }

    public function scopeCanRollback($query)
    {
        return $query->where('can_rollback', true)
                     ->whereNull('rolled_back_at');
    }

    public function scopeRolledBack($query)
    {
        return $query->whereNotNull('rolled_back_at');
    }

    public function getChangeTypeLabelAttribute(): string
    {
        return match ($this->change_type) {
            'base_price' => 'Base Price',
            'period_pricing' => 'Period Pricing',
            'fee' => 'Fee',
            'tax' => 'Tax',
            'discount' => 'Discount',
            'availability' => 'Availability',
            'bulk_update' => 'Bulk Update',
            default => 'Other',
        };
    }

    public function getChangeSourceLabelAttribute(): string
    {
        return match ($this->change_source) {
            'manual' => 'Manual',
            'api' => 'API',
            'bulk_import' => 'Bulk Import',
            'automation' => 'Automation',
            'external_sync' => 'External Sync',
            'system' => 'System',
            default => 'Unknown',
        };
    }
}
