<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccommodationDraft extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'data',
        'current_step',
        'last_saved_at',
    ];

    public function photos(): HasMany
    {
        return $this->hasMany(AccommodationDraftPhoto::class);
    }
}
