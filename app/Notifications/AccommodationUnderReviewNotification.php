<?php

namespace App\Notifications;

use App\Enums\Notification\NotificationType;
use App\Models\AccommodationDraft;

class AccommodationUnderReviewNotification extends InAppNotification
{
    public function __construct(
        protected AccommodationDraft $draft,
    ) {}

    public function toData(): array
    {
        $data = json_decode($this->draft->data, true);

        return [
            'type' => NotificationType::AccommodationUnderReview->value,
            'draft_id' => $this->draft->id,
            'title' => $data['title'] ?? null,
        ];
    }
}
