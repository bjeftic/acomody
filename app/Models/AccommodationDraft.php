<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccommodationDraft extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'data',
        'current_step',
        'last_saved_at',
    ];

    public function canBeReadBy($user): bool
    {
        return $user && $user->id === $this->user_id;
    }

    public function canBeCreatedBy($user): bool
    {
        return $user !== null;
    }

    public function canBeUpdatedBy($user): bool
    {
        return $user && $user->id === $this->user_id;
    }

    public function canBeDeletedBy($user): bool
    {
        return $user && $user->id === $this->user_id;
    }

    public function photos(): HasMany
    {
        return $this->hasMany(AccommodationDraftPhoto::class);
    }
}
