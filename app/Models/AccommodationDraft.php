<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AccommodationDraft extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'data',
        'current_step',
        'last_saved_at',
    ];
}
