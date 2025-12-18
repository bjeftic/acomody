<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use Spatie\Translatable\HasTranslations;

class Amenity extends Model
{
    use HasActiveScope, HasTranslations;

    public $translatable = ['name'];

    protected $fillable = [
        'slug',
        'name',
        'icon',
        'category',
        'type',
        'is_feeable',
        'is_active',
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
}
