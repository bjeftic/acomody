<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccommodationType;
use App\Http\Resources\AccommodationTypeResource;
use App\Http\Support\ApiResponse;
use App\Enums\AccommodationOccupation;

class AccommodationTypeController extends Controller
{
    /**
     * Get accommodation types list
     *
     * @OA\Get(
     *     path="/accommodation-types",
     *     operationId="getAccommodationTypes",
     *     tags={"Accommodation"},
     *     summary="Get accommodation types list",
     *     description="Returns a list of all accommodation types",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Accommodation types retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Accommodation types retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                      property="accommodation_types",
     *                      type="array",
     *                      @OA\Items(ref="#/components/schemas/AccommodationType")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="email_verified", type="boolean", example=true),
     *                 @OA\Property(property="profile_complete", type="boolean", example=true),
     *                 @OA\Property(property="account_status", type="string", example="active")
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $accommodationTypes = AccommodationType::all();

        $meta = [
            'total' => $accommodationTypes->count(),
        ];

        return ApiResponse::success(
            'Accommodation types retrieved successfully.',
            AccommodationTypeResource::collection($accommodationTypes),
            $meta
        );
    }
}
