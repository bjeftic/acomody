<?php


namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AmenityResource extends JsonResource
{
    /**
     * @OA\Schema(
     *     schema="Amenity",
     *     type="object",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="slug", type="string", example="wifi"),
     *     @OA\Property(property="name", type="string", example="WiFi"),
     *     @OA\Property(property="icon", type="string", example="wifi"),
     *     @OA\Property(property="category", type="string", example="internet"),
     *     @OA\Property(property="is_feeable", type="boolean", example=false),
     *     @OA\Property(property="is_highlighted", type="boolean", example=true),
     *     @OA\Property(property="is_active", type="boolean", example=true)
     * )
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'icon' => $this->icon,
            'category' => $this->category,
            'is_feeable' => $this->is_feeable,
            'is_highlighted' => $this->is_highlighted,
            'is_active' => $this->is_active,
        ];
    }
}
