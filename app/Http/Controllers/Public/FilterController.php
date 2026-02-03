<?php

namespace App\Http\Controllers\Public;

use App\Services\FilterService;
use App\Http\Resources\AmenityResource;
use App\Http\Support\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\FilterResource;

class FilterController extends Controller
{
    protected FilterService $filterService;

    public function __construct(FilterService $filterService)
    {
        $this->filterService = $filterService;
    }

    public function index()
    {
        return collect([
            $this->filterService->getAccommodationCategoryFilters(),
            $this->filterService->getAccommodationOccupationFilters(),
            $this->filterService->getAmenityFilters()
        ])->values();
    }
}
