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
                'latitude' => $document['location']['1'] ?? null,
                'longitude' => $document['location']['0'] ?? null,
            ],
            'is_featured' => $document['is_featured'] ?? false,
            'price' => $userSettedCurrency === $document['currency'] ? $document['regular_price'] : calculatePriceInSettedCurrency($document['regular_price'], $document['currency'], $userSettedCurrency),
            'rating' => $document['rating'] ?? null,
            'images' => $document['images'] ?? [],
            'location_id' => $document['location_id'] ?? null,
            'accommodation_type' => $document['accommodation_type'] ?? null,
            'accommodation_occupation' => $document['accommodation_occupation'] ?? null,
            'max_guests' => $document['max_guests'] ?? null,
        ];
    }
}
