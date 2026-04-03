<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;

/**
 * @property string $user_id
 * @property int|null $locked_by_id
 * @property \Illuminate\Support\Carbon|null $locked_at
 */
class AccommodationDraft extends Model
{
    use HasFactory, HasUlids;

    /** Lock expires after this many minutes of inactivity. */
    public const LOCK_DURATION_MINUTES = 30;

    protected $fillable = [
        'user_id',
        'status',
        'data',
        'current_step',
        'last_saved_at',
        'locked_by_id',
        'locked_at',
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

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by_id');
    }

    /**
     * Whether this draft is actively locked by a different admin.
     */
    public function isLockedByAnother(User $user): bool
    {
        if (! $this->locked_by_id || $this->locked_by_id === $user->id) {
            return false;
        }

        return $this->locked_at !== null
            && $this->locked_at->copy()->addMinutes(self::LOCK_DURATION_MINUTES)->isFuture();
    }

    /**
     * Acquire (or refresh) the review lock for the given admin.
     */
    public function acquireLock(User $user): void
    {
        $this->update([
            'locked_by_id' => $user->id,
            'locked_at' => now(),
        ]);
    }

    /**
     * Release the review lock.
     */
    public function releaseLock(): void
    {
        $this->update([
            'locked_by_id' => null,
            'locked_at' => null,
        ]);
    }

    /**
     * Returns the timestamp at which the current lock expires.
     */
    public function lockExpiresAt(): ?Carbon
    {
        if (! $this->locked_at) {
            return null;
        }

        return $this->locked_at->copy()->addMinutes(self::LOCK_DURATION_MINUTES);
    }

    protected function casts(): array
    {
        return [
            'locked_at' => 'datetime',
        ];
    }
}
