<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccommodationDraft\UpdateRequest;
use App\Http\Requests\AccommodationDraft\CreateRequest;
use App\Http\Requests\AccommodationDraft\IndexRequest;
use App\Http\Resources\AccommodationDraftResource;
use App\Http\Support\ApiResponse;
use App\Models\AccommodationDraft;
use App\Services\AccommodationService;
use Illuminate\Http\JsonResponse;

class AccommodationDraftController extends Controller
{
    protected AccommodationService $accommodationService;

    public function __construct(AccommodationService $accommodationService)
    {
        $this->accommodationService = $accommodationService;
    }

    /**
     * Create accommodation draft
     * @OA\Post(
     *     path="/accommodation-draft",
     *     operationId="createAccommodationDraft",
     *     tags={"Accommodation"},
     *     summary="Create accommodation draft",
     *     description="Creates a new draft for an accommodation listing",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Accommodation draft data",
     *         @OA\JsonContent(
     *             required={"data"},
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 example={"title": "Cozy Apartment", "description": "A nice place to stay"},
     *                 description="The draft data for the accommodation listing"
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Accommodation draft created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/AccommodationDraft")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function createDraft(CreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $accommodationDraft = $this->accommodationService->createAccommodationDraft(
            userOrFail()->id,
            $data['data']
        );

        return ApiResponse::success(
            'Accommodation draft created successfully',
            new AccommodationDraftResource($accommodationDraft),
            null,
            201
        );
    }


    /**
     * Update accommodation draft
     *
     * @OA\Put(
     *     path="/accommodation-draft/{accommodationDraft}",
     *     operationId="updateAccommodationDraft",
     *     tags={"Accommodation"},
     *     summary="Update accommodation draft",
     *     description="Updates a draft for an accommodation listing",
     *     security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="accommodationDraft",
     *         in="path",
     *         required=true,
     *         description="Accommodation draft ID",
     *         @OA\Schema(type="string", format="uuid", example="019a4b7b-3481-738a-a2ff-d93fc45bac01")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Accommodation draft data",
     *         @OA\JsonContent(
     *             required={"current_step","data"},
     *             @OA\Property(
     *                 property="current_step",
     *                 type="integer",
     *                 example=2,
     *                 description="The current step in the accommodation listing process"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 example={"title": "Cozy Apartment", "description": "A nice place to stay"},
     *                 description="The draft data for the accommodation listing"
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Accommodation draft saved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/AccommodationDraft")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function updateDraft(UpdateRequest $request, AccommodationDraft $accommodationDraft): JsonResponse
    {
        $data = $request->validated();
        $accommodationDraft = $this->accommodationService->updateAccommodationDraft(
            $accommodationDraft,
            $data['data'],
            $data['current_step'],
            $data['status']
        );

        return ApiResponse::success(
            'Accommodation draft saved successfully',
            new AccommodationDraftResource($accommodationDraft)
        );
    }

    /**
     * Get accommodation drafts
     * @OA\Get(
     *     path="/accommodation-draft",
     *     operationId="getAccommodationDrafts",
     *     tags={"Accommodation"},
     *     summary="Get accommodation drafts",
     *     description="Retrieves a list of accommodation drafts for the authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by draft status",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"draft", "waiting_for_approval", "published"},
     *             default="draft"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Accommodation drafts retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/AccommodationDraft")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $status = $request->getStatus() ?? 'draft';

        $accommodationDrafts = $this->accommodationService->getAccommodationDrafts(
            userOrFail()->id,
            $status
        );

        return ApiResponse::success(
            'Accommodation drafts retrieved successfully',
            AccommodationDraftResource::collection($accommodationDrafts)
        );
    }

    /**
     * Get accommodation draft
     *
     * @OA\Get(
     *     path="/accommodation-draft/draft",
     *     operationId="getAccommodationDraft",
     *     tags={"Accommodation"},
     *     summary="Get accommodation draft",
     *     description="Retrieves the saved draft for an accommodation listing",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Accommodation draft retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/AccommodationDraft")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Draft not found"
     *     )
     * )
     */
    public function getAccommodationDraft(): JsonResponse
    {
        $accommodationDraft = $this->accommodationService->getAccommodationDraft(
            userOrFail()->id,
            'draft'
        );

        return ApiResponse::success(
            'Accommodation draft retrieved successfully',
            new AccommodationDraftResource($accommodationDraft)
        );
    }

    /**
     * Get accommodation draft statistics
     *
     * @OA\Get(
     *     path="/accommodation-drafts/stats",
     *     operationId="getAccommodationDraftStats",
     *     tags={"Accommodation"},
     *     summary="Get accommodation draft statistics",
     *     description="Retrieves statistics about accommodation drafts",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Accommodation draft statistics retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Draft statistics by status",
     *                 example={"draft": 3, "waiting_for_approval": 1, "published": 5}
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(
     *                     property="total_drafts",
     *                     type="integer",
     *                     example=9,
     *                     description="Total number of accommodation drafts"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function getDraftStats()
    {
        $accommodationDraftStats = $this->accommodationService->getAccommodationDraftStats(
            userOrFail()->id,
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Accommodation draft statistics retrieved successfully',
            'data' => $accommodationDraftStats,
            'meta' => [
                'total_drafts' => array_sum($accommodationDraftStats),
            ]
        ]);
    }
}
