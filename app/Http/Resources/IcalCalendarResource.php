<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IcalCalendarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'accommodation_id' => $this->accommodation_id,
            'source' => $this->source->value,
            'source_label' => $this->source->label(),
            'name' => $this->name,
            'ical_url' => $this->ical_url,
            'is_active' => $this->is_active,
            'last_synced_at' => $this->last_synced_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
