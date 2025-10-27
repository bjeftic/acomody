<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
        'icon_library',
        'category',
        'type',
        'is_active',
    ];}
