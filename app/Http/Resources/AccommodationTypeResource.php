<?php


namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\AccommodationOccupation;

/**
 * @OA\Schema(
 *     schema="AccommodationType",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Apartment"),
 *     @OA\Property(property="icon", type="string", example="Apartment"),
 *     @OA\Property(property="description", type="string", example="A private, self-contained unit within a building, featuring its own kitchen, bathroom, and living area—ideal for independent stays."),
 *     @OA\Property(
 *         property="available_occupations",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="An entire place"),
 *             @OA\Property(property="description", type="string", example="Guests have the whole place to themselves.")
 *         )
 *     )
 * )
 */
class AccommodationTypeResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'icon' => $this->icon,
            'available_occupations' => collect(AccommodationOccupation::forAccommodationType($this->resource))
                ->map(fn($occupation) => [
                    'id' => $occupation->id(),
                    'name' => $occupation->label(),
                    'description' => $occupation->description(),
                ])
        ];
    }
}
