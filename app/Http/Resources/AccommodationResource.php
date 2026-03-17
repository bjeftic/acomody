<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Schema(
 *     schema="Accommodation",
 *     type="object",
 *
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
 *
 *         @OA\Items(ref="#/components/schemas/Amenity")
 *     ),
 *
 *     @OA\Property(
 *         property="photos",
 *         type="array",
 *
 *         @OA\Items(ref="#/components/schemas/Photo")
 *     ),
 *
 *     @OA\Property(
 *         property="price",
 *         type="array",
 *
 *         @OA\Items(ref="#/components/schemas/Price")
 *     ),
 *
 *     @OA\Property(property="views_count", type="integer", example=142),
 *     @OA\Property(property="favorites_count", type="integer", example=17),
 * )
 *
 * @mixin \App\Models\Accommodation
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
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'title' => $this->title,
            'description' => $this->description,
            'amenities' => AmenityResource::collection($this->amenities ?? []),
            'photos' => PhotoResource::collection($this->photos ?? []),
            'pricing' => $this->pricing ? new PriceResource($this->pricing) : null,
            'views_count' => $this->views_count,
            'favorites_count' => $this->favorites_count,
            'cancellation_policy' => $this->cancellation_policy,
            'booking_type' => $this->booking_type,
            'ical_token' => $this->ical_token,
            'ical_export_active' => $this->ical_export_active,
            'check_in_from' => $this->check_in_from,
            'check_in_until' => $this->check_in_until,
            'check_out_until' => $this->check_out_until,
            'quiet_hours_from' => $this->quiet_hours_from,
            'quiet_hours_until' => $this->quiet_hours_until,
            'beds' => $this->when(
                $this->relationLoaded('beds'),
                fn () => $this->beds->map(fn ($bed) => [
                    'bed_type' => $bed->bed_type->value,
                    'quantity' => $bed->quantity,
                ])->values()
            ),
            'location' => $this->when(
                $this->relationLoaded('location') && $this->location,
                fn () => [
                    'name' => $this->location->getTranslation('name', 'en'),
                    'country_code' => $this->location->country?->iso_code_2,
                ]
            ),
            'host' => $this->host_profile ? [
                'id' => $this->host_profile->id,
                'display_name' => $this->host_profile->display_name,
                'bio' => $this->host_profile->bio,
                'avatar_url' => $this->host_profile->avatar
                    ? Storage::disk('user_profile_photos')->url($this->host_profile->avatar)
                    : null,
            ] : null,
        ];
    }
}
