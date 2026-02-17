<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\CurrencyService;

class PriceResource extends JsonResource
{
    /**
     * @OA\Schema(
     *     schema="Price",
     *     type="object",
     *     @OA\Property(property="pricing_type", type="string", example="per_unit"),
     *     @OA\Property(property="base_price", type="number", format="float", example=29.99),
     *     @OA\Property(property="currency", type="string", example="USD"),
     *     @OA\Property(property="base_price_eur", type="number", format="float", example=27.50),
     *     @OA\Property(property="price", type="number", format="float", example=27.50),
     *     @OA\Property(property="rounded_price", type="integer", example=28),
     *     @OA\Property(property="min_quantity", type="integer", example=1),
     *     @OA\Property(property="max_quantity", type="integer", example=100),
     *     @OA\Property(property="is_active", type="boolean", example=true),
     *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-28T12:34:56Z"),
     *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-28T12:50:00Z"),
     * )
     */
    public function toArray(Request $request): array
    {
        $userSettedCurrency = CurrencyService::getUserCurrency()->code;

        return [
            'pricing_type' => $this->pricing_type,
            'base_price' => $this->base_price,
            'currency' => $this->currency,
            'base_price_eur' => $this->base_price_eur,
            'price' => $userSettedCurrency === 'EUR'
                ? round($this->base_price_eur, 2)
                : round(calculatePriceInSettedCurrency(
                    $this->base_price_eur,
                    'EUR',
                    $userSettedCurrency
                ), 2),
            'rounded_price' => ceil($userSettedCurrency === 'EUR'
                ? round($this->base_price_eur, 2)
                : round(calculatePriceInSettedCurrency(
                    $this->base_price_eur,
                    'EUR',
                    $userSettedCurrency
                ), 2)),
            'min_quantity' => $this->min_quantity,
            'max_quantity' => $this->max_quantity,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
