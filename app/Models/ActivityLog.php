<?php

namespace App\Models;

use App\Enums\Activity\ActivityEvent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'event',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
    ];

    protected function casts(): array
    {
        return [
            'event' => ActivityEvent::class,
            'properties' => 'array',
        ];
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where(function (Builder $q) use ($userId) {
            $q->where(function (Builder $q2) use ($userId) {
                $q2->where('subject_type', User::class)->where('subject_id', $userId);
            })->orWhere(function (Builder $q2) use ($userId) {
                $q2->where('causer_type', User::class)->where('causer_id', $userId);
            });
        });
    }

    public function scopeEvent(Builder $query, ActivityEvent $event): Builder
    {
        return $query->where('event', $event->value);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where('description', 'like', "%{$term}%");
    }
}
