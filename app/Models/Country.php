<?php

namespace App\Models;
class Country extends Model
{
    public function locations()
    {
        return $this->hasMany(Location::class);
    }
}
