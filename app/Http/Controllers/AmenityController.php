<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use App\Http\Resources\AmenityResource;
use App\Http\Support\ApiResponse;

class AmenityController extends Controller
{
    /**
     * @OA\Get(
     *     path="/amenities",
     *     operationId="getAmenities",
     *     tags={"Amenity"},
     *     summary="Get amenities list",
     *     description="Returns a list of all amenities",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Amenities retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Amenities retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Amenity")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="total", type="integer", example=15)
     *             )
     *         )
     *     )
     * )
     */

    public function index()
    {
        $amenities = Amenity::all()->sortBy('name');

        $meta = [
            'total' => $amenities->count(),
        ];

        return ApiResponse::success(
            'Amenities retrieved successfully.',
            AmenityResource::collection($amenities),
            $meta
        );
    }
}
