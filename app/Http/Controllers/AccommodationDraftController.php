<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccommodationDraft\UpdateRequest;
use App\Http\Requests\AccommodationDraft\CreateRequest;
use App\Http\Requests\AccommodationDraft\IndexRequest;
use App\Http\Requests\AccommodationDraft\PhotoUploadRequest;
use Illuminate\Http\Request;
use App\Http\Resources\AccommodationDraftResource;
use App\Http\Resources\PhotoResource;
use App\Http\Support\ApiResponse;
use App\Models\AccommodationDraft;
use App\Models\Photo;
use App\Services\AccommodationService;
use App\Services\PhotoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Validation\ValidationException;

class AccommodationDraftController extends Controller
{
    protected AccommodationService $accommodationService;
    protected PhotoService $photoService;

    public function __construct(AccommodationService $accommodationService, PhotoService $photoService)
    {
        $this->accommodationService = $accommodationService;
        $this->photoService = $photoService;
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
     *         @OA\Schema(type="string", format="ulid", example="019a4b7b-3481-738a-a2ff-d93fc45bac01")
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

    /**
     * Get all photos for accommodation draft
     *
     * @OA\Get(
     *     path="/accommodation-drafts/{accommodationDraftId}/photos",
     *     operationId="getAccommodationDraftPhotos",
     *     tags={"Accommodation"},
     *     summary="Get all photos for accommodation draft",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="accommodationDraftId",
     *         in="path",
     *         required=true,
     *         description="Accommodation draft ID",
     *         @OA\Schema(type="string", format="ulid", example="019a4b7b-3481-738a-a2ff-d93fc45bac01")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Photos retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Photos retrieved successfully."),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Photo"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Accommodation draft not found")
     * )
     */
    public function indexPhotos(AccommodationDraft $accommodationDraft): JsonResponse
    {
        try {
            $photos = $accommodationDraft->photos()
                ->orderBy('order')
                ->get();

            return ApiResponse::success(
                'Photos retrieved successfully.',
                PhotoResource::collection($photos)
            );
        } catch (Exception $e) {
            Log::error('Failed to retrieve photos', [
                'accommodation_draft_id' => $accommodationDraft->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error('Failed to retrieve photos.', null, null, 500);
        }
    }

    /**
     * Upload photos
     *
     * @OA\Post(
     *     path="/accommodation-drafts/{accommodationDraftId}/photos",
     *     operationId="uploadAccommodationDraftPhotos",
     *     tags={"Accommodation"},
     *     summary="Upload photos for accommodation draft",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="accommodationDraftId",
     *         in="path",
     *         required=true,
     *         description="Accommodation draft ID",
     *         @OA\Schema(type="string", format="ulid", example="019a4b7b-3481-738a-a2ff-d93fc45bac01")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"photos"},
     *                 @OA\Property(
     *                     property="photos[]",
     *                     type="array",
     *                     description="Array of photo files (max 10 files, 10MB each)",
     *                     @OA\Items(type="string", format="binary")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Photos uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Photos uploaded successfully."),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Photo")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="uploaded_count", type="integer", example=3),
     *                 @OA\Property(property="failed_count", type="integer", example=0),
     *                 @OA\Property(property="total_photos", type="integer", example=8)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function storePhotos(
        PhotoUploadRequest $request,
        AccommodationDraft $accommodationDraft
    ): JsonResponse {
        try {
            $files = $request->file('photos');

            // Get current max order
            $maxOrder = $accommodationDraft->photos()->max('order') ?? -1;
            $startOrder = $maxOrder + 1;

            // Upload photos
            $result = $this->photoService->uploadMultiplePhotos(
                $accommodationDraft,
                $files,
                $startOrder
            );

            // If no photos were uploaded successfully
            if ($result['success_count'] === 0) {
                return ApiResponse::error(
                    'All photo uploads failed.',
                    null,
                    [
                        'failed_photos' => $result['failed'],
                    ],
                    422
                );
            }

            Log::info('Photos uploaded', [
                'accommodation_draft_id' => $accommodationDraft->id,
                'success_count' => $result['success_count'],
                'failed_count' => $result['failed_count'],
                'user_id' => userOrFail()->id,
            ]);

            // Prepare response message
            $message = 'Photos uploaded successfully.';
            if ($result['failed_count'] > 0) {
                $message = "{$result['success_count']} photo(s) uploaded successfully, {$result['failed_count']} failed.";
            }

            return ApiResponse::success(
                $message,
                PhotoResource::collection($result['uploaded']),
                [
                    'uploaded_count' => $result['success_count'],
                    'failed_count' => $result['failed_count'],
                    'total_photos' => $accommodationDraft->photos()->count(),
                    'failed_photos' => $result['failed'],
                ],
                201
            );
        } catch (Exception $e) {
            Log::error('Photo upload failed', [
                'accommodation_draft_id' => $accommodationDraft->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error(
                'Failed to upload photos.',
                null,
                null,
                500
            );
        }
    }

    /**
     * Reorder photos
     *
     * @OA\Put(
     *     path="/accommodation-drafts/{accommodationDraftId}/photos/reorder",
     *     operationId="reorderAccommodationDraftPhotos",
     *     tags={"Accommodation"},
     *     summary="Reorder photos for accommodation draft",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="accommodationDraftId",
     *         in="path",
     *         required=true,
     *         description="Accommodation draft ID",
     *         @OA\Schema(type="string", format="ulid", example="019a4b7b-3481-738a-a2ff-d93fc45bac01")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"photo_ids"},
     *             @OA\Property(
     *                 property="photo_ids",
     *                 type="array",
     *                 description="Array of photo IDs in desired order",
     *                 @OA\Items(type="string", format="ulid"),
     *                 example={"019a4b7b-3481-738a-a2ff-d93fc45bac01", "019a4b7b-3481-738a-a2ff-d93fc45bac02"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Photos reordered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Photos reordered successfully."),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Photo"))
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Failed to reorder photos")
     * )
     */
    public function reorder(
        Request $request,
        AccommodationDraft $accommodationDraft
    ): JsonResponse {
        try {
            $validated = $request->validate([
                'photo_ids' => 'required|array|min:1',
                'photo_ids.*' => 'required|ulid|exists:photos,id',
            ]);

            // Verify all photos belong to this accommodation draft
            $photos = Photo::whereIn('id', $validated['photo_ids'])
                ->where('photoable_type', AccommodationDraft::class)
                ->where('photoable_id', $accommodationDraft->id)
                ->get();

            if ($photos->count() !== count($validated['photo_ids'])) {
                return ApiResponse::error(
                    'One or more photos do not belong to this accommodation draft.',
                    null,
                    null,
                    422
                );
            }

            $this->photoService->reorderPhotos(
                $accommodationDraft,
                $validated['photo_ids']
            );

            $photos = $accommodationDraft->photos()
                ->orderBy('order')
                ->get();

            return ApiResponse::success(
                'Photos reordered successfully.',
                PhotoResource::collection($photos)
            );
        } catch (ValidationException $e) {
            return ApiResponse::error(
                'Validation failed.',
                $e->errors(),
                null,
                422
            );
        } catch (Exception $e) {
            Log::error('Failed to reorder photos', [
                'accommodation_draft_id' => $accommodationDraft->id,
                'photo_ids' => $request->input('photo_ids'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error(
                'Failed to reorder photos.',
                null,
                null,
                500
            );
        }
    }

    /**
     * Delete a photo
     *
     * @OA\Delete(
     *     path="/accommodation-drafts/{accommodationDraftId}/photos/{photoId}",
     *     operationId="deleteAccommodationDraftPhoto",
     *     tags={"Accommodation"},
     *     summary="Delete a photo from accommodation draft",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="accommodationDraftId",
     *         in="path",
     *         required=true,
     *         description="Accommodation draft ID",
     *         @OA\Schema(type="string", format="ulid", example="019a4b7b-3481-738a-a2ff-d93fc45bac01")
     *     ),
     *     @OA\Parameter(
     *         name="photoId",
     *         in="path",
     *         required=true,
     *         description="Photo ID",
     *         @OA\Schema(type="string", format="ulid", example="019a4b7b-3481-738a-a2ff-d93fc45bac01")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Photo deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Photo deleted successfully."),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="total_photos", type="integer", example=5)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Photo not found"),
     *     @OA\Response(response=500, description="Failed to delete photo")
     * )
     */
    public function destroy(
        AccommodationDraft $accommodationDraft,
        Photo $photo
    ): JsonResponse {
        try {
            // Verify photo belongs to the accommodation draft
            if (
                $photo->photoable_type !== AccommodationDraft::class ||
                $photo->photoable_id !== $accommodationDraft->id
            ) {
                return ApiResponse::error('Photo not found.', null, null, 404);
            }

            $this->photoService->deletePhoto($photo);

            return ApiResponse::success(
                'Photo deleted successfully.',
                null,
                [
                    'total_photos' => $accommodationDraft->photos()->count(),
                ]
            );
        } catch (Exception $e) {
            Log::error('Failed to delete photo', [
                'accommodation_draft_id' => $accommodationDraft->id,
                'photo_id' => $photo->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error(
                'Failed to delete photo.',
                null,
                null,
                500
            );
        }
    }

    /**
     * Get photo statistics
     *
     * @OA\Get(
     *     path="/accommodation-drafts/{accommodationDraftId}/photos/stats",
     *     operationId="getAccommodationDraftPhotoStats",
     *     tags={"Accommodation"},
     *     summary="Get photo statistics for accommodation draft",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="accommodationDraftId",
     *         in="path",
     *         required=true,
     *         description="Accommodation draft ID",
     *         @OA\Schema(type="string", format="ulid", example="019a4b7b-3481-738a-a2ff-d93fc45bac01")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Statistics retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Photo statistics retrieved successfully."),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="total", type="integer", example=10),
     *                 @OA\Property(property="completed", type="integer", example=8),
     *                 @OA\Property(property="pending", type="integer", example=1),
     *                 @OA\Property(property="failed", type="integer", example=1),
     *                 @OA\Property(property="has_primary", type="boolean", example=true),
     *                 @OA\Property(property="total_size", type="integer", example=5242880),
     *                 @OA\Property(property="total_size_formatted", type="string", example="5.00 MB")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Failed to get statistics")
     * )
     */
    public function stats(AccommodationDraft $accommodationDraft): JsonResponse
    {
        try {
            $stats = $this->photoService->getPhotoStats($accommodationDraft);

            return ApiResponse::success(
                'Photo statistics retrieved successfully.',
                null,
                $stats
            );
        } catch (Exception $e) {
            Log::error('Failed to get photo stats', [
                'accommodation_draft_id' => $accommodationDraft->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error('Failed to get statistics.', null, null, 500);
        }
    }
}
