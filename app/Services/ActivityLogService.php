<?php

namespace App\Services;

use App\Enums\Activity\ActivityEvent;
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogService
{
    /**
     * Log an activity event.
     *
     * @param  array<string, mixed>  $properties
     */
    public static function log(
        ActivityEvent $event,
        string $description,
        ?Model $subject = null,
        ?Model $causer = null,
        array $properties = [],
    ): ActivityLog {
        return ActivityLog::create([
            'event' => $event->value,
            'description' => $description,
            'subject_type' => $subject ? $subject->getMorphClass() : null,
            'subject_id' => $subject?->getKey(),
            'causer_type' => $causer ? $causer->getMorphClass() : null,
            'causer_id' => $causer?->getKey(),
            'properties' => empty($properties) ? null : $properties,
        ]);
    }
}
