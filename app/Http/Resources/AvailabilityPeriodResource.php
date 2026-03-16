<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AvailabilityPeriodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'accommodation_id' => $this->available_id,
            'start_date' => $this->start_date->toDateString(),
            'end_date' => $this->end_date->toDateString(),
            'status' => $this->status,
            'notes' => $this->notes,
            'is_ical_synced' => $this->ical_calendar_id !== null,
            'ical_calendar_name' => $this->icalCalendar?->name,
        ];
    }
}
