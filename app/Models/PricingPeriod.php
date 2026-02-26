<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

/**
 * @property string $period_type
 * @property \Carbon\Carbon|null $start_date
 * @property \Carbon\Carbon|null $end_date
 */
class PricingPeriod extends Model
{
    use HasUlids;

    protected $fillable = [
        'priceable_type',
        'priceable_id',
        'period_type',
        'name',
        'description',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'applicable_days',
        'price_override',
        'price_multiplier',
        'price_adjustment',
        'min_quantity_override',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'applicable_days' => 'array',
        'price_override' => 'decimal:2',
        'price_multiplier' => 'decimal:2',
        'price_adjustment' => 'decimal:2',
        'min_quantity_override' => 'integer',
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

    public function priceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('period_type', $type);
    }

    public function scopeForDate($query, Carbon $date)
    {
        return $query->where(function ($q) use ($date) {
            $q->where(function ($subQ) use ($date) {
                // Date-based periods
                $subQ->whereNotNull('start_date')
                     ->where('start_date', '<=', $date)
                     ->where('end_date', '>=', $date);
            })->orWhere(function ($subQ) use ($date) {
                // Recurring day-of-week periods
                $subQ->whereNull('start_date')
                     ->whereJsonContains('applicable_days', strtolower($date->format('l')));
            });
        });
    }

    public function scopeForEntity($query, string $entityType, string $entityId)
    {
        return $query->where('priceable_type', $entityType)
                     ->where('priceable_id', $entityId);
    }

    public function scopeOrderByPriority($query, string $direction = 'desc')
    {
        return $query->orderBy('priority', $direction);
    }

    public function getPeriodTypeLabelAttribute(): string
    {
        return match ($this->period_type) {
            'seasonal' => 'Seasonal',
            'special_date' => 'Special Date',
            'time_of_day' => 'Time of Day',
            'day_of_week' => 'Day of Week',
            'happy_hour' => 'Happy Hour',
            'event' => 'Event',
            default => 'Custom',
        };
    }

    public function getDateRangeAttribute(): string
    {
        if (!$this->start_date || !$this->end_date) {
            return 'N/A';
        }
        return $this->start_date->format('M d, Y') . ' - ' . $this->end_date->format('M d, Y');
    }
}
