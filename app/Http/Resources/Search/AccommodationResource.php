<?php


namespace App\Http\Resources\Search;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\CurrencyService;

class AccommodationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $document = $this->resource['document'] ?? $this->resource;
        $userSettedCurrency = CurrencyService::getUserCurrency()->code;

        return [
            'id' => $document['id'] ?? null,
            'title' => $document['title'] ?? null,
            'location_id' => $document['location_id'] ?? null,
            'coordinates' => [
                'latitude' => $document['location']['0'] ?? null,
                'longitude' => $document['location']['1'] ?? null,
            ],
            'is_featured' => $document['is_featured'] ?? false,
            'base_price_eur' => $document['base_price_eur'] ?? null,
            'price' => $userSettedCurrency === 'EUR'
                ? round($document['base_price_eur'], 2)
                : round(calculatePriceInSettedCurrency(
                    $document['base_price_eur'],
                    'EUR',
                    $userSettedCurrency
                ), 2),
            'runded_price' => ceil($userSettedCurrency === 'EUR'
                ? round($document['base_price_eur'], 2)
                : round(calculatePriceInSettedCurrency(
                    $document['base_price_eur'],
                    'EUR',
                    $userSettedCurrency
                ), 2)),
            'rating' => $document['rating'] ?? null,
            'images' => $document['images'] ?? [],
            'accommodation_type' => $document['accommodation_type'] ?? null,
            'accommodation_occupation' => $document['accommodation_occupation'] ?? null,
            'max_guests' => $document['max_guests'] ?? null,
        ];
    }
}
