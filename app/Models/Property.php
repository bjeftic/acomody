<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Property extends Model
{
    use HasFactory;

    public function listing()
    {
        return $this->morphOne(Listing::class, 'listable');
    }

    public function propertyType()
    {
        return $this->belongsTo(PropertyType::class);
    }
}
