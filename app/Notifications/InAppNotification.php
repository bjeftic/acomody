<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Base class for all in-app (database-stored) notifications.
 *
 * To add a new notification type:
 *   1. Add a case to App\Enums\Notification\NotificationType
 *   2. Create a class extending InAppNotification
 *   3. Implement toData() returning the payload array (must include 'type' key)
 *   4. Inject the notification via $user->notify(new YourNotification($model))
 */
abstract class InAppNotification extends Notification
{
    use Queueable;

    /**
     * @return array<int, string>
     */
    final public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Build the notification payload stored in the database.
     *
     * @return array<string, mixed>
     */
    abstract public function toData(): array;

    /**
     * @return array<string, mixed>
     */
    final public function toArray(object $notifiable): array
    {
        return $this->toData();
    }
}
