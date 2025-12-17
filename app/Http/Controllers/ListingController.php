<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Listing\IndexRequest;
use App\Http\Resources\ListingResource;
use App\Services\ListingService;
use App\Http\Support\ApiResponse;

class ListingController extends Controller
{
    protected ListingService $listingService;

    public function __construct(ListingService $listingService)
    {
        $this->listingService = $listingService;
    }

    /**
     * Get listings
     * @OA\Get(
     *     path="/listings",
     *     operationId="getListings",
     *     tags={"Listing"},
     *     summary="Get listings",
     *     description="Retrieves a paginated list of listings for the authenticated user",
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
     *         description="Listings retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Listing")),
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
        $listings = $this->listingService-> getListings(
            userOrFail()->id,
            $request->getPerPage()
        );

        // return response()->json([
        //     'success' => true,
        //     'message' => 'Listings retrieved successfully',
        //     'data' => ListingResource::collection($listings->items()),
        //     'pagination' => [
        //         'current_page' => $listings->currentPage(),
        //         'last_page' => $listings->lastPage(),
        //         'per_page' => $listings->perPage(),
        //         'total' => $listings->total(),
        //         'next_page_url' => $listings->nextPageUrl(),
        //         'prev_page_url' => $listings->previousPageUrl(),
        //     ]
        // ]);

        // return ApiResponse::success(
        //     'Listings retrieved successfully',
        //     ListingResource::collection($listings)->response()->getData(true)
        // );

        return ApiResponse::success(
            'Listings retrieved successfully',
            ListingResource::collection($listings)
        );
    }
}
