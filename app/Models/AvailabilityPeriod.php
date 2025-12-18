<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class AvailabilityPeriod extends Model
{
    use HasUlids;

    protected $fillable = [
        'available_type',
        'available_id',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'recurring_days',
        'status',
        'reason',
        'notes',
        'max_capacity',
        'current_bookings',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'recurring_days' => 'array',
        'max_capacity' => 'integer',
        'current_bookings' => 'integer',
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

    public function available(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeBlocked($query)
    {
        return $query->where('status', 'blocked');
    }

    public function scopeBooked($query)
    {
        return $query->where('status', 'booked');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeForDate($query, Carbon $date)
    {
        return $query->where('start_date', '<=', $date)
                     ->where('end_date', '>=', $date);
    }

    public function scopeForDateRange($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->where('start_date', '<=', $endDate)
              ->where('end_date', '>=', $startDate);
        });
    }

    public function scopeForEntity($query, string $entityType, string $entityId)
    {
        return $query->where('available_type', $entityType)
                     ->where('available_id', $entityId);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'available' => 'Available',
            'blocked' => 'Blocked',
            'booked' => 'Booked',
            'maintenance' => 'Maintenance',
            'closed' => 'Closed',
            'sold_out' => 'Sold Out',
            default => 'Unknown',
        };
    }

    public function getReasonLabelAttribute(): string
    {
        if (!$this->reason) return 'N/A';

        return match ($this->reason) {
            'owner_blocked' => 'Blocked by Owner',
            'maintenance' => 'Maintenance',
            'booking' => 'Booking',
            'external_booking' => 'External Booking',
            'holiday' => 'Holiday',
            'closed_day' => 'Closed Day',
            'capacity_reached' => 'Capacity Reached',
            'weather' => 'Weather',
            'event' => 'Private Event',
            default => 'Other',
        };
    }

    public function getDateRangeAttribute(): string
    {
        return $this->start_date->format('M d, Y') . ' - ' . $this->end_date->format('M d, Y');
    }

    public function getRemainingCapacityAttribute(): ?int
    {
        if (!$this->max_capacity) return null;
        return max(0, $this->max_capacity - $this->current_bookings);
    }
}
