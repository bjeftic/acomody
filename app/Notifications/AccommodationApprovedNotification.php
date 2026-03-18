<?php

namespace App\Notifications;

use App\Enums\Notification\NotificationType;
use App\Models\Accommodation;

class AccommodationApprovedNotification extends InAppNotification
{
    public function __construct(
        protected Accommodation $accommodation,
    ) {}

    public function toData(): array
    {
        return [
            'type' => NotificationType::AccommodationApproved->value,
            'accommodation_id' => $this->accommodation->id,
            'title' => $this->accommodation->title,
        ];
    }
}
