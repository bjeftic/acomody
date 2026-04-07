<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;

class Amenity extends Model
{
    use HasActiveScope, HasFactory, HasTranslations;

    public $translatable = ['name'];

    protected $fillable = [
        'slug',
        'name',
        'icon',
        'category',
        'is_feeable',
        'is_highlighted',
        'is_optional',
        'is_active',
        'sort_order',
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

    public function accommodations()
    {
        return $this->belongsToMany(Accommodation::class, 'accommodation_amenity');
    }
}
