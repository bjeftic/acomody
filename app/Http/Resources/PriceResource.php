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
     *     @OA\Property(
     *         property="base_price",
     *         type="object",
     *         @OA\Property(property="base_price", type="number", format="float", example=29.99),
     *         @OA\Property(property="currency", type="string", example="USD")
     *     ),
     *     @OA\Property(
     *         property="base_price_eur",
     *         type="object",
     *         @OA\Property(property="base_price", type="number", format="float", example=27.50)
     *     ),
     *     @OA\Property(
     *         property="base_price_in_user_currency",
     *         type="object",
     *         @OA\Property(property="base_price", type="number", format="float", example=27.50),
     *         @OA\Property(property="base_price_rounded", type="integer", example=28),
     *         @OA\Property(property="currency", ref="#/components/schemas/Currency")
     *     ),
     *     @OA\Property(property="min_quantity", type="integer", example=1),
     *     @OA\Property(property="max_quantity", type="integer", example=100),
     *     @OA\Property(property="is_active", type="boolean", example=true),
     *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-28T12:34:56Z"),
     *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-28T12:50:00Z")
     * )
     */
    public function toArray(Request $request): array
    {
        $userSettedCurrency = CurrencyService::getUserCurrency();

        return [
            'pricing_type' => $this->pricing_type,
            'base_price' => [
                'base_price' => $this->base_price,
                'currency' => $this->currency,
            ],
            'base_price_eur' => [
                'base_price' => $this->base_price_eur,
            ],
            'base_price_in_user_currency' => [
                'base_price' => $userSettedCurrency->code === 'EUR'
                    ? round($this->base_price_eur, 2)
                    : round(calculatePriceInSettedCurrency(
                        $this->base_price_eur,
                        'EUR',
                        $userSettedCurrency->code
                    ), 2),
                'base_price_rounded' => ceil($userSettedCurrency->code === 'EUR'
                    ? round($this->base_price_eur, 2)
                    : round(calculatePriceInSettedCurrency(
                        $this->base_price_eur,
                        'EUR',
                        $userSettedCurrency->code
                    ), 2)),
                'currency' => new CurrencyResource($userSettedCurrency),
            ],
            'min_quantity' => $this->min_quantity,
            'max_quantity' => $this->max_quantity,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
