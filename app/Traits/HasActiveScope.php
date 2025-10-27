<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasActiveScope
{
    protected static function bootHasActiveScope()
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('is_active', true);
        });
    }

    public function scopeWithInactive($query)
    {
        return $query->withoutGlobalScope('active');
    }
}
