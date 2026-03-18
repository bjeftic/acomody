<?php

namespace App\Http\Controllers;

use App\Enums\Accommodation\BedType;
use App\Http\Resources\BedTypeResource;
use App\Http\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class BedTypeController extends Controller
{
    /**
     * Get all bed types.
     */
    public function index(): JsonResponse
    {
        $bedTypes = collect(BedType::cases());

        return ApiResponse::success(
            'Bed types retrieved successfully.',
            BedTypeResource::collection($bedTypes),
            ['total' => $bedTypes->count()]
        );
    }
}
