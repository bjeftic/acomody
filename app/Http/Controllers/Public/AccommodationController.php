<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccommodationResource;
use App\Http\Support\ApiResponse;
use App\Models\Accommodation;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\AccommodationService;
use Illuminate\Http\JsonResponse;

class AccommodationController extends Controller
{
    public function __construct(private readonly AccommodationService $accommodationService) {}

    public function show(Accommodation $accommodation): JsonResponse
    {
        return ApiResponse::success(
            'Accommodation retrieved successfully',
            new AccommodationResource($this->accommodationService->fetchAccommodation($accommodation->id))
        );
    }
}
