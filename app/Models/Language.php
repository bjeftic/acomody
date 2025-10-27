<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use Spatie\Translatable\HasTranslations;

class Language extends Model
{
    use HasActiveScope, HasTranslations;

    public $translatable = ['name'];
}
