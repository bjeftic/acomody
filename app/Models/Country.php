<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use Spatie\Translatable\HasTranslations;

class Country extends Model
{
    use HasActiveScope, HasTranslations;

    public $translatable = ['name'];

    public function canBeReadBy($user): bool
    {
        return true;
    }

    public function canBeCreatedBy($user): bool
    {
        return $user !== null && $user->is_superadmin;
    }

    public function canBeUpdatedBy($user): bool
    {
        return $user !== null && $user->is_superadmin;
    }

    public function canBeDeletedBy($user): bool
    {
        return $user !== null && $user->is_superadmin;
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }
}
