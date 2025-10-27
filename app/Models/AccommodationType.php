<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;
use App\Enums\AccommodationOccupation;

class AccommodationType extends Model
{
    use HasFactory, HasActiveScope, HasTranslations;

    protected $fillable = ['name', 'description'];

    public $translatable = ['name', 'description'];

    public function accommodations()
    {
        return $this->hasMany(Accommodation::class);
    }

    public function availableOccupations(): array
    {
        return AccommodationOccupation::forAccommodationType($this);
    }
}
