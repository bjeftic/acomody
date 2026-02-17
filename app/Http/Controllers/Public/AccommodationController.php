<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accommodation;
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

        return ApiResponse::success(
            'Accommodation retrieved successfully',
            new AccommodationResource($accommodation->load('amenities', 'photos', 'pricing'))
        );
    }
}
