<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="Listing",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="ulid", example="3fa85f64-5717-4562-b3fc-2c963f66afa6"),
 *     @OA\Property(property="current_step", type="integer", example=2),
 *     @OA\Property(property="status", type="string", example="draft"),
 *     @OA\Property(property="data", type="object", example={"title": "Cozy Apartment", "description": "A nice place to stay"}),
 *     @OA\Property(property="last_saved_at", type="string", format="date-time", example="2025-10-28T12:34:56Z"),
 * )
 */
class ListingResource extends JsonResource
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
            'listable_type' => class_basename($this->listable_type),
            'listable_id' => $this->listable_id,
            'listable' => $this->whenLoaded('listable', function () {
                $resourceClass = 'App\\Http\\Resources\\' . class_basename($this->listable_type) . 'Resource';
                if (class_exists($resourceClass)) {
                    return new $resourceClass($this->listable);
                }
                return $this->listable;
            }),
            'address' => $this->street_address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }
}
