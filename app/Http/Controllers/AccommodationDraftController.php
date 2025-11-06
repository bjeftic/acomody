<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccommodationDraft\SaveDraftRequest;
use App\Http\Resources\AccommodationDraftResource;
use App\Services\AccommodationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AccommodationDraftController extends Controller
{
    protected AccommodationService $accommodationService;

    public function __construct(AccommodationService $accommodationService)
    {
        $this->accommodationService = $accommodationService;
    }

    /**
     * Save accommodation draft
     *
     * @OA\Post(
     *     path="/accommodation-draft",
     *     operationId="saveAccommodationDraft",
     *     tags={"Accommodation"},
     *     summary="Save accommodation draft",
     *     description="Saves a draft for an accommodation listing",
     *     security={{"bearerAuth":{}}},
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
    public function saveDraft(SaveDraftRequest $request)
    {
        $data = $request->validated();
        $accommodationDraft = $this->accommodationService->saveAccommodationDraft(
            userOrFail()->id,
            $data['data'],
            $data['current_step'],
            'draft'
        );

        return new AccommodationDraftResource($accommodationDraft);
    }

    /**
     * Get accommodation draft
     *
     * @OA\Get(
     *     path="/accommodation-draft",
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
     *     )
     * )
     */
    public function getDraft(): AccommodationDraftResource
    {
        $accommodationDraft = $this->accommodationService->getAccommodationDraft(
            userOrFail()->id
        );
        return new AccommodationDraftResource($accommodationDraft);
    }
}
