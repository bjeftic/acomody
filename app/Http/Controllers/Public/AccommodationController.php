<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;
use App\Http\Support\ApiResponse;
use App\Http\Resources\AccommodationResource;

class AccommodationController extends Controller
{
    public function show(Accommodation $accommodation): JsonResponse
    {
        if (!$accommodation) {
            return ApiResponse::error('Accommodation not found', null, null, 404);
        }

        UserProfile::withoutAuthorization(
            fn () => $accommodation->load('amenities', 'photos', 'pricing', 'user.userProfile')
        );

        return ApiResponse::success(
            'Accommodation retrieved successfully',
            new AccommodationResource($accommodation)
        );
    }
}
