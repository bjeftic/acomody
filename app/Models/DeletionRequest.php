<?php

namespace App\Models;

use App\Enums\DeletionRequestStatus;
use App\Enums\DeletionRequestType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property DeletionRequestStatus $status
 * @property DeletionRequestType $type
 */
class DeletionRequest extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'subject_id',
        'status',
        'reason',
        'processed_by',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => DeletionRequestType::class,
            'status' => DeletionRequestStatus::class,
            'processed_at' => 'datetime',
        ];
    }

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
        return false;
    }

    public function canBeDeletedBy($user): bool
    {
        return false;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function isPending(): bool
    {
        return $this->status === DeletionRequestStatus::Pending;
    }
}
