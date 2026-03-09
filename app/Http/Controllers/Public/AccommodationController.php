<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\FeaturedAccommodationsRequest;
use App\Http\Resources\AccommodationResource;
use App\Http\Resources\Search\AccommodationResource as SearchAccommodationResource;
use App\Http\Support\ApiResponse;
use App\Models\Accommodation;
use App\Services\AccommodationService;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;

class AccommodationController extends Controller
{
    public function __construct(
        private readonly AccommodationService $accommodationService,
        private readonly SearchService $searchService,
    ) {}

    public function index(FeaturedAccommodationsRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $sortBy = $this->searchService->getSortByFilter($validated['sortBy'] ?? 'rating');

        $filters = [];

        if (! empty($validated['location_id'])) {
            $filters[] = "location_id:={$validated['location_id']}";
        }

        $filterBy = ! empty($filters) ? implode(' && ', $filters) : null;

        $results = $this->searchService->searchCollection('accommodations', '*', [
            'filter_by' => $filterBy,
            'sort_by' => $sortBy,
            'page' => $validated['page'] ?? 1,
            'per_page' => $validated['perPage'] ?? 12,
        ]);

        return response()->json([
            'hits' => SearchAccommodationResource::collection(collect($results['hits'] ?? [])),
            'found' => $results['found'] ?? 0,
            'page' => $results['page'] ?? 1,
            'per_page' => count($results['hits'] ?? []),
        ]);
    }

    public function show(Accommodation $accommodation): JsonResponse
    {
        return ApiResponse::success(
            'Accommodation retrieved successfully',
            new AccommodationResource($this->accommodationService->fetchAccommodation($accommodation->id))
        );
    }
}
