<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;

class ReviewComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'commentable_id',
        'commentable_type',
        'user_id',
        'body',
    ];

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function canBeReadBy($user): bool
    {
        // The superadmin who wrote the comment
        if ($user && $user->id === $this->user_id) {
            return true;
        }

        // The owner of the commentable (e.g. the host whose draft was reviewed)
        if ($user !== null && $this->commentable_type === AccommodationDraft::class) {
            return DB::table('accommodation_drafts')
                ->where('id', $this->commentable_id)
                ->where('user_id', $user->id)
                ->exists();
        }

        return false;
    }

    public function canBeCreatedBy($user): bool
    {
        return $user !== null && $user->is_superadmin;
    }

    public function canBeUpdatedBy($user): bool
    {
        return $user !== null && $user->is_superadmin;
    }

    public function canBeDeletedBy($user): bool
    {
        return $user && $user->id === $this->user_id && $user->is_superadmin;
    }
}
