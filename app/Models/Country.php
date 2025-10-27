<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use Spatie\Translatable\HasTranslations;

class Country extends Model
{
    use HasActiveScope, HasTranslations;

    public $translatable = ['name'];

    public function locations()
    {
        return $this->hasMany(Location::class);
    }
}
