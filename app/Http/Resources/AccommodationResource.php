<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="Accommodation",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="ulid", example="3fa85f64-5717-4562-b3fc-2c963f66afa6"),
 *     @OA\Property(property="current_step", type="integer", example=2),
 *     @OA\Property(property="status", type="string", example="draft"),
 *     @OA\Property(property="data", type="object", example={"title": "Cozy Apartment", "description": "A nice place to stay"}),
 *     @OA\Property(property="last_saved_at", type="string", format="date-time", example="2025-10-28T12:34:56Z"),
 * )
 */
class AccommodationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'address' => $this->street_address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'accommodation_type' => $this->accommodation_type,
            'accommodation_occupation' => $this->accommodation_occupation,
            'max_guests' => $this->max_guests,
            'title' => $this->title,
            'description' => $this->description,
            'amenities' => json_decode($this->amenities, true) ?? [],
            'views_count' => $this->views_count,
            'favorites_count' => $this->favorites_count,
        ];
    }
}
