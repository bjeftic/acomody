<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\AccommodationOccupation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Accommodation extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'accommodation_type_id',
        'accommodation_occupation_id',
        'title',
        'description',
        'accommodation_draft_id',
        'user_id',
        'amenities',
    ];

    protected $casts = [
        'accommodation_occupation_id' => AccommodationOccupation::class,
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

    public function hasValidOccupationType(): bool
    {
        return $this->occupation_type->isValidFor($this->accommodationType);
    }
}
