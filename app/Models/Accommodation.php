<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\AccommodationOccupation;

class Accommodation extends Model
{
    use HasFactory;

    protected $fillable = [
        'accommodation_type_id',
        'occupation_type',
        'amenities',
    ];

    protected $casts = [
        'occupation_type' => AccommodationOccupation::class,
    ];

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
