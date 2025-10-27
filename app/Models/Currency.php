<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use Spatie\Translatable\HasTranslations;

class Currency extends Model
{
    use HasActiveScope, HasTranslations;

    public $translatable = ['name'];
}
