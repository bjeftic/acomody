<?php


namespace App\Http\Resources\Fee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeeTypeResource extends JsonResource
{
    /**
     * @OA\Schema(
     *     schema="FeeType",
     *     type="object",
     *     @OA\Property(property="name", type="string", example="CLEANING"),
     *    @OA\Property(property="label", type="string", example="Cleaning Fee"),
     *    @OA\Property(property="listing", type="string", example="accommodation"),
     *     @OA\Property(property="value", type="string", example="cleaning")
     * )
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->resource['name'],
            'description' => $this->resource['description'],
            'value' => $this->resource['value'],
            'listing' => $this->resource['listing'],
        ];
    }
}
