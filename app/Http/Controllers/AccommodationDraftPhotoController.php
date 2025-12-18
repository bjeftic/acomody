<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PhotoUploadService;
use App\Http\Controllers\Controller;
use App\Http\Requests\AccommodationDraft\PhotoUploadRequest;
use App\Http\Resources\AccommodationDraftPhotoResource;
use App\Http\Support\ApiResponse;
use App\Models\AccommodationDraft;
use App\Models\AccommodationDraftPhoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class AccommodationDraftPhotoController extends Controller
{
    protected PhotoUploadService $photoService;

    public function __construct(PhotoUploadService $photoService)
    {
        $this->photoService = $photoService;
    }

    /**
     * Get all photos for a draft
     *
     * @OA\Get(
     *     path="/accommodation-drafts/{accommodationDraftId}/photos",
     *     operationId="getAccommodationDraftPhotos",
     *     tags={"Accommodation"},
     *     summary="Get all photos for accommodation draft",
     *    security={{"bearerAuth":{}}},
     *  @OA\Parameter(
     *         name="accommodationDraft",
     *         in="path",
     *         required=true,
     *         description="Accommodation draft ID",
     *         @OA\Schema(type="string", format="ulid", example="019a4b7b-3481-738a-a2ff-d93fc45bac01")
     *     ),
     *    @OA\Response(
     *         response=200,
     *         description="Accommodation draft photos retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/AccommodationDraftPhoto")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *    @OA\Response(
     *        response=404,
     *       description="Accommodation draft photos not found"
     *   )
     * )
     */
    public function index(AccommodationDraft $accommodationDraft): JsonResponse
    {
        try {
            $photos = $accommodationDraft->photos()
                ->ordered()
                ->get();

            return ApiResponse::success(
                'Photos retrieved successfully.',
                AccommodationDraftPhotoResource::collection($photos)
            );
        } catch (Exception $e) {
            Log::error('Failed to retrieve photos', [
                'draft_id' => $accommodationDraft->id,
                'error' => $e->getMessage(),
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
     *  @OA\Parameter(
     *         name="accommodationDraft",
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
     *                 @OA\Property(
     *                     property="photos[]",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Photos uploaded successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(
        PhotoUploadRequest $request,
        AccommodationDraft $accommodationDraft
    ): JsonResponse {
        try {
            $files = $request->file('photos');

            // Get current max order
            $maxOrder = $accommodationDraft->photos()->max('order') ?? -1;
            $startOrder = $maxOrder + 1;

            // Upload photos - returns array with 'uploaded', 'failed', counts
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
                'draft_id' => $accommodationDraft->id,
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
                AccommodationDraftPhotoResource::collection($result['uploaded']), // ✅ Use 'uploaded' key
                [
                    'uploaded_count' => $result['success_count'],
                    'failed_count' => $result['failed_count'],
                    'total_photos' => $accommodationDraft->photos()->count(),
                    'failed_photos' => $result['failed'], // Include failed details
                ],
                201
            );
        } catch (Exception $e) {
            Log::error('Photo upload failed', [
                'draft_id' => $accommodationDraft->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error(
                'Failed to upload photos: ' . $e->getMessage(),
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
     *     summary="Reorder photos",
     *     security={{"bearerAuth":{}}},
     *  @OA\Parameter(
     *         name="accommodationDraft",
     *         in="path",
     *         required=true,
     *         description="Accommodation draft ID",
     *         @OA\Schema(type="string", format="ulid", example="019a4b7b-3481-738a-a2ff-d93fc45bac01")
     *     ),
     *     @OA\Parameter(
     *         name="accommodationDraft",
     *         in="path",
     *         required=true,
     *         description="Accommodation draft ID",
     *         @OA\Schema(type="string", format="ulid", example="019a4b7b-3481-738a-a2ff-d93fc45bac01")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="photo_ids",
     *                 type="array",
     *                 @OA\Items(type="integer"),
     *                 example={1,3,2,4}
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Photos reordered successfully")
     * )
     */
    public function reorder(
        Request $request,
        AccommodationDraft $accommodationDraft
    ): JsonResponse {
        try {

            $validated = $request->validate([
                'photo_ids' => 'required|array',
                'photo_ids.*' => 'required|integer|exists:accommodation_draft_photos,id',
            ]);

            $success = $this->photoService->reorderPhotos(
                $accommodationDraft,
                $validated['photo_ids']
            );

            if (!$success) {
                return ApiResponse::error('Failed to reorder photos.', null, null, 500);
            }

            $photos = $accommodationDraft->photos()
                ->ordered()
                ->get();

            return ApiResponse::success(
                'Photos reordered successfully.',
                AccommodationDraftPhotoResource::collection($photos)
            );
        } catch (Exception $e) {
            Log::error('Failed to reorder photos', [
                'draft_id' => $accommodationDraft->id,
                'error' => $e->getMessage(),
            ]);

            return ApiResponse::error('Failed to reorder photos.', null, null, 500);
        }
    }

    /**
     * Delete a photo
     *
     * @OA\Delete(
     *     path="/accommodation-drafts/{accommodationDraftId}/photos/{photoId}",
     *     operationId="deleteAccommodationDraftPhoto",
     *     tags={"Accommodation"},
     *     summary="Delete a photo",
     *     security={{"bearerAuth":{}}},
     *  @OA\Parameter(
     *         name="accommodationDraft",
     *         in="path",
     *         required=true,
     *         description="Accommodation draft ID",
     *         @OA\Schema(type="string", format="ulid", example="019a4b7b-3481-738a-a2ff-d93fc45bac01")
     *     ),
     *     @OA\Response(response=200, description="Photo deleted successfully")
     * )
     */
    public function destroy(
        AccommodationDraft $accommodationDraft,
        AccommodationDraftPhoto $photo
    ): JsonResponse {
        try {
            // Verify photo belongs to draft
            if ($photo->accommodation_draft_id !== $accommodationDraft->id) {
                return ApiResponse::error('Photo not found.', null, null, 404);
            }

            $success = $this->photoService->deletePhoto($photo);

            if (!$success) {
                return ApiResponse::error('Failed to delete photo.', null, null, 500);
            }

            return ApiResponse::success(
                'Photo deleted successfully.',
                null,
                [
                    'total_photos' => $accommodationDraft->photos()->count(),
                ]
            );
        } catch (Exception $e) {
            Log::error('Failed to delete photo', [
                'photo_id' => $photo->id,
                'error' => $e->getMessage(),
            ]);

            return ApiResponse::error('Failed to delete photo.', null, null, 500);
        }
    }

    /**
     * Get photo statistics
     *
     * @OA\Get(
     *     path="/accommodation-drafts/{accommodationDraftId}/photos/stats",
     *     operationId="getAccommodationDraftPhotoStats",
     *     tags={"Accommodation"},
     *     summary="Get photo statistics",
     *     security={{"bearerAuth":{}}},
     *  @OA\Parameter(
     *         name="accommodationDraft",
     *         in="path",
     *         required=true,
     *         description="Accommodation draft ID",
     *         @OA\Schema(type="string", format="ulid", example="019a4b7b-3481-738a-a2ff-d93fc45bac01")
     *     ),
     *     @OA\Response(response=200, description="Statistics retrieved successfully")
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
                'draft_id' => $accommodationDraft->id,
                'error' => $e->getMessage(),
            ]);

            return ApiResponse::error('Failed to get statistics.', null, null, 500);
        }
    }
}
