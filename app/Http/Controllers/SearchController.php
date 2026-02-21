<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Search\SearchLocationRequest;
use App\Http\Requests\Search\SearchAccommodationRequest;
use App\Http\Resources\Search\AccommodationResource;
use App\Http\Resources\Search\AccommodationFacetResource;
use App\Models\Location;
use App\Services\CurrencyService;
use App\Services\SearchService;

class SearchController extends Controller
{
    protected SearchService $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function searchLocations(SearchLocationRequest $request)
    {
        $query = $request->input('q');
        $options = [
            'locations' => [
                'query_by' => 'name',
                // 'filter_by' => 'location_type:=city',
                'per_page' => 5,
            ]
        ];

        $results = $this->searchService->searchCollection('locations', $query, $options['locations']);

        return response()->json($results);
    }

    public function searchAccommodations(SearchAccommodationRequest $request): array
    {
        $validated = $request->validated();

        $filters = [];
        $sortBy = null;

        // sort by except location sort
        $sortBy = $this->searchService->getSortByFilter($validated['sortBy'] ?? 'price_asc');

        if (!empty($validated['bounds'])) {
            // Coordinate-based search
            // Typesense expects: field_name:(lat1, lng1, lat2, lng2)
            $neLat = $validated['bounds']['northEast']['lat'];
            $neLng = $validated['bounds']['northEast']['lng'];
            $swLat = $validated['bounds']['southWest']['lat'];
            $swLng = $validated['bounds']['southWest']['lng'];

            // Build bounding box filter
            // Note: Typesense uses geopoint format: [lat, lng]
            $filters[] = "location:({$neLat}, {$swLng}, {$neLat}, {$neLng}, {$swLat}, {$neLng}, {$swLat}, {$swLng})";
        } elseif (!empty($validated['location']['id'])) {
            // need to fix this
            $location = Location::find($validated['location']['id']);
            $filters[] = "location_id:={$validated['location']['id']}";
            // $sortBy .= ",location($location->longitude, $location->latitude):asc";
        }

        if (!empty($validated['accommodation_categories'])) {
            $categoryFilters = array_map(
                fn($categoryId) => "accommodation_category:={$categoryId}",
                (array) $validated['accommodation_categories']
            );
            $filters[] = "(" . implode(' || ', $categoryFilters) . ")";
        }

        if (!empty($validated['accommodation_occupations'])) {
            $occupationFilters = array_map(
                fn($occupationId) => "accommodation_occupation:={$occupationId}",
                (array) $validated['accommodation_occupations']
            );
            $filters[] = "(" . implode(' || ', $occupationFilters) . ")";
        }
        if (!empty($validated['guests'])) {
            $totalGuests = ($validated['guests']['adults'] ?? 0) +
                ($validated['guests']['children'] ?? 0) +
                ($validated['guests']['infants'] ?? 0);

            if ($totalGuests > 0) {
                $filters[] = "max_guests:>={$totalGuests}";
            }
        }

        if (!empty($validated['amenities'])) {
            $amenityFilters = array_map(
                fn($amenityId) => "amenities:={$amenityId}",
                $validated['amenities']
            );
            $filters[] = "(" . implode(' || ', $amenityFilters) . ")";
        }

        $filterBy = !empty($filters) ? implode(' && ', $filters) : null;

        $firstResults = $this->searchService->searchCollection('accommodations', '*',  [
            // 'query_by' => 'name,description,address',  // Text fields!
            'filter_by' => $filterBy,
            'sort_by' => $sortBy,
            'facet_by' => 'base_price_eur',
            'page' => $validated['page'] ?? 1,
            'per_page' => $validated['perPage'] ?? 10,
        ]);

        if ($firstResults['found'] == 0) {
            return [
                'facet_counts' => [],
                'hits' => [],
                'found' => 0,
                'page' => $validated['page'] ?? 1,
                'per_page' => $validated['perPage'] ?? 10,
            ];
        }

        $priceRangeEntry = collect($validated)
            ->filter(fn($_, $key) => str_starts_with($key, 'priceRange_'))
            ->first();

        $priceRangeKey = collect($validated)
            ->filter(fn($_, $key) => str_starts_with($key, 'priceRange_'))
            ->keys()
            ->first();

        $currency = str_replace('priceRange_', '', $priceRangeKey);

        $min = $priceRangeEntry['min'] ?? null;
        $max = $priceRangeEntry['max'] ?? null;

        $priceQuery = '';
        $minInUserCurrency = null;
        $maxInUserCurrency = null;
        if (isset($min) || isset($max)) {
            // Get latest exchange rates

            $priceQuery .= '(';
            if (isset($min)) {
                if ($currency === 'EUR') {
                    $convertedMin = $min;
                } else {
                    $convertedMin = ceil(calculatePriceInSettedCurrency($min, $currency, 'EUR'));
                }
                $priceQuery .= "base_price_eur:>={$convertedMin}";
            }
            if (isset($max)) {
                if (isset($min)) {
                    $priceQuery .= ' && ';
                }
                if ($currency === 'EUR') {
                    $convertedMax = $max;
                } else {
                    $convertedMax = ceil(calculatePriceInSettedCurrency($max, $currency, 'EUR'));
                }
                $priceQuery .= "base_price_eur:<={$convertedMax}";
            }
            $priceQuery .= ')';

            $minInUserCurrency = $min !== null
                ? calculatePriceInSettedCurrency($min, $currency, CurrencyService::getUserCurrency()->code)
                : null;
            $maxInUserCurrency = $max !== null
                ? calculatePriceInSettedCurrency($max, $currency, CurrencyService::getUserCurrency()->code)
                : null;
        }

        if (!empty($priceQuery)) {
            $filterBy = $filterBy . ' && ' . $priceQuery;
        }

        $secondResults = $this->searchService->searchCollection('accommodations', '*',  [
            // 'query_by' => 'name,description,address',  // Text fields!
            'filter_by' => $filterBy,
            'sort_by' => $sortBy,
            'max_facet_values' => 100,
            'page' => $validated['page'] ?? 1,
            'per_page' => $validated['perPage'] ?? 10,
        ]);

        // Fix currency in price facets
        $firstResults['facet_counts'] = array_map(function ($facet) use ($minInUserCurrency, $maxInUserCurrency) {
            $settedCurrencyCode = CurrencyService::getUserCurrency()->code;
            if ($facet['field_name'] === 'base_price_eur') {
                $facet['field_name'] = 'price';
                $facet['counts'] = array_map(function ($count) use ($settedCurrencyCode) {
                    $convertedValue = calculatePriceInSettedCurrency((float)$count['value'], 'EUR', $settedCurrencyCode);
                    return [
                        'value' => $convertedValue !== null ? (string)$convertedValue : null,
                        'count' => $count['count'],
                    ];
                }, $facet['counts']);
                $facet['stats'] = [
                    'filter_max' => $maxInUserCurrency ?? null,
                    'filter_min' => $minInUserCurrency ?? null,
                    'min' => ceil(calculatePriceInSettedCurrency($facet['stats']['min'], 'EUR', $settedCurrencyCode)),
                    'max' => ceil(calculatePriceInSettedCurrency($facet['stats']['max'], 'EUR', $settedCurrencyCode)),
                    'avg' => calculatePriceInSettedCurrency((float)$facet['stats']['avg'], 'EUR', $settedCurrencyCode),
                    'sum' => calculatePriceInSettedCurrency((float)$facet['stats']['sum'], 'EUR', $settedCurrencyCode),
                    'total_values' => $facet['stats']['total_values'],
                ];
            }
            return $facet;
        }, $firstResults['facet_counts'] ?? []);

        $unifiedFacetCounts = array_merge(
            $firstResults['facet_counts'] ?? [],
            $secondResults['facet_counts'] ?? []
        );

        $facets = [];
        foreach ($unifiedFacetCounts as $facetTemp) {
            array_push($facets, new AccommodationFacetResource($facetTemp));
        }

        return [
            'facet_counts' => $facets,
            'hits' => AccommodationResource::collection(collect($secondResults['hits'] ?? [])),
            'found' => $secondResults['found'] ?? 0,
            'page' => $secondResults['page'] ?? 1,
            'per_page' => count($secondResults['hits'] ?? []),
        ];
    }
}
