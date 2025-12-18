<?php


namespace App\Http\Resources\Fee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class  FeeChargeTypeResource extends JsonResource
{
    /**
     * @OA\Schema(
     *     schema="FeeChargeType",
     *     type="object",
     *     @OA\Property(property="value", type="string", example="per_unit"),
     *     @OA\Property(property="name", type="string", example="PER_UNIT"),
     *     @OA\Property(property="label", type="string", example="Per Unit"),
     *     @OA\Property(property="description", type="string", example="Charge applied per unit, such as per night, per hour, or per item.")
     * )
     */
    public function toArray(Request $request): array
    {
        return [
            'value' => $this->resource['value'],
            'name' => $this->resource['name'],
            'description' => $this->resource['description'],
        ];
    }
}
