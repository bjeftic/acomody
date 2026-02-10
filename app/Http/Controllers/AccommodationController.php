<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Accommodation\IndexRequest;
use App\Http\Resources\AccommodationResource;
use App\Http\Resources\PhotoResource;
use App\Services\AccommodationService;
use App\Http\Support\ApiResponse;
use App\Models\Accommodation;
use Illuminate\Support\Facades\Log;
use Exception;

class AccommodationController extends Controller
{
    protected AccommodationService $accommodationService;

    public function __construct(AccommodationService $accommodationService)
    {
        $this->accommodationService = $accommodationService;
    }

    /**
     * Get accommodations
     * @OA\Get(
     *     path="/accommodations",
     *     operationId="getAccommodations",
     *     tags={"Accommodation"},
     *     summary="Get accommodations",
     *     description="Retrieves a paginated list of accommodations for the authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Accommodations retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Accommodation")),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="last_page", type="integer"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="total", type="integer"),
     *             @OA\Property(property="next_page_url", type="string", nullable=true),
     *             @OA\Property(property="prev_page_url", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $accommodations = $this->accommodationService-> getAccommodations(
            userOrFail()->id,
            $request->getPerPage()
        );

        return ApiResponse::success(
            'Accommodations retrieved successfully',
            AccommodationResource::collection($accommodations)
        );
    }

    /**
     * Get a specific listing by ID
     * @OA\Get(
     *     path="/accommodations/{accommodation}",
     *     operationId="getAccommodationById",
     *     tags={"Accommodation"},
     *     summary="Get a specific accommodation by ID",
     *     description="Retrieves a specific accommodation for the authenticated user by its ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="accommodation",
     *         in="path",
     *         description="ID of the accommodation to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Accommodation retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Accommodation")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Accommodation not found"
     *     )
     * )
     */
    public function show(Accommodation $accommodation): JsonResponse
    {
        $accommodation = $this->accommodationService->getAccommodationById(
            userOrFail()->id,
            $accommodation->id
        );

        if (!$accommodation) {
            return ApiResponse::error('Accommodation not found', null, null, 404);
        }

        return ApiResponse::success(
            'Accommodation retrieved successfully',
            new AccommodationResource($accommodation)
        );
    }

    public function indexPhotos(Accommodation $accommodation): JsonResponse
    {
        try {
            $photos = $accommodation->photos()
                ->ordered()
                ->get();

            return ApiResponse::success(
                'Accommodation photos retrieved successfully.',
                PhotoResource::collection($photos)
            );
        } catch (Exception $e) {
            Log::error('Failed to retrieve photos', [
                'draft_id' => $accommodation->id,
                'error' => $e->getMessage(),
            ]);

            return ApiResponse::error('Failed to retrieve photos.', null, null, 500);
        }
    }
}
