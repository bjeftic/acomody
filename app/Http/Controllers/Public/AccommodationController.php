<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccommodationResource;
use App\Http\Support\ApiResponse;
use App\Models\Accommodation;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;

class AccommodationController extends Controller
{
    public function show(Accommodation $accommodation): JsonResponse
    {
        Accommodation::withoutAuthorization(
            fn () => $accommodation->load('amenities', 'photos', 'pricing', 'user.userProfile')
        );

        return ApiResponse::success(
            'Accommodation retrieved successfully',
            new AccommodationResource($accommodation)
        );
    }
}
