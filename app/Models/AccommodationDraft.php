<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property string $user_id
 */
class AccommodationDraft extends Model
{
    use HasFactory, HasUlids;

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

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'photoable')
            ->orderBy('order');
    }

    public function primaryPhoto(): MorphOne
    {
        return $this->morphOne(Photo::class, 'photoable')
            ->where('is_primary', true);
    }

    public function reviewComments(): MorphMany
    {
        return $this->morphMany(ReviewComment::class, 'commentable')
            ->latest();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
