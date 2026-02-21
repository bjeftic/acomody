<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="Accommodation",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="ulid", example="01ARZ3NDEKTSV4RRFFQ69G5FAV"),
 *     @OA\Property(property="address", type="string", example="123 Main Street"),
 *     @OA\Property(property="latitude", type="number", format="float", example=44.8125),
 *     @OA\Property(property="longitude", type="number", format="float", example=20.4612),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="is_featured", type="boolean", example=false),
 *     @OA\Property(property="accommodation_type", type="string", example="apartment"),
 *     @OA\Property(property="accommodation_occupation", type="string", example="entire_place"),
 *     @OA\Property(property="max_guests", type="integer", example=4),
 *     @OA\Property(property="title", type="string", example="Cozy Apartment in City Center"),
 *     @OA\Property(property="description", type="string", example="A nice place to stay"),
 *     @OA\Property(
 *         property="amenities",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Amenity")
 *     ),
 *     @OA\Property(
 *         property="photos",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Photo")
 *     ),
 *     @OA\Property(
 *         property="price",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Price")
 *     ),
 *     @OA\Property(property="views_count", type="integer", example=142),
 *     @OA\Property(property="favorites_count", type="integer", example=17),
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
            'amenities' => AmenityResource::collection($this->whenLoaded('amenities')),
            'photos' => PhotoResource::collection($this->whenLoaded('photos')),
            'pricing' => new PriceResource($this->whenLoaded('pricing')),
            'views_count' => $this->views_count,
            'favorites_count' => $this->favorites_count,
        ];
    }
}
