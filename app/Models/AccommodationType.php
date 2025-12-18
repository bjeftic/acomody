<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;
use App\Enums\Accommodation\AccommodationOccupation;

class AccommodationType extends Model
{
    use HasFactory, HasActiveScope, HasTranslations;

    protected $fillable = ['name', 'description'];

    public $translatable = ['name', 'description'];

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

    public function accommodations()
    {
        return $this->hasMany(Accommodation::class);
    }

    public function availableOccupations(): array
    {
        return AccommodationOccupation::forAccommodationType($this);
    }
}
