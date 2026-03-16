<?php

namespace App\Models;

use App\Enums\Ical\IcalSource;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property Accommodation $accommodation
 */
class IcalCalendar extends Model
{
    use HasUlids;

    protected $fillable = [
        'accommodation_id',
        'source',
        'name',
        'ical_url',
        'is_active',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'source' => IcalSource::class,
            'is_active' => 'boolean',
            'last_synced_at' => 'datetime',
        ];
    }

    public function canBeReadBy($user): bool
    {
        return $user !== null && $this->accommodation->user_id === $user->id;
    }

    public function canBeCreatedBy($user): bool
    {
        return $user !== null;
    }

    public function canBeUpdatedBy($user): bool
    {
        return $user !== null && $this->accommodation->user_id === $user->id;
    }

    public function canBeDeletedBy($user): bool
    {
        return $user !== null && $this->accommodation->user_id === $user->id;
    }

    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }

    public function availabilityPeriods(): HasMany
    {
        return $this->hasMany(AvailabilityPeriod::class);
    }
}
