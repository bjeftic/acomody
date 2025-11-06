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
     *     path="/accommodation-drafts/{id}/photos",
     *     operationId="getAccommodationDraftPhotos",
     *     tags={"Accommodation Draft Photo"},
     *     summary="Get all photos for accommodation draft",
     *    security={{"bearerAuth":{}}},
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
     *     path="/accommodation-drafts/{id}/photos",
     *     operationId="uploadAccommodationDraftPhotos",
     *     tags={"Accommodation Draft Photo"},
     *     summary="Upload photos for accommodation draft",
     *     security={{"bearerAuth":{}}},
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
     *     path="/accommodation-drafts/{id}/photos/reorder",
     *     operationId="reorderAccommodationDraftPhotos",
     *     tags={"Accommodation Draft Photo"},
     *     summary="Reorder photos",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Accommodation draft ID",
     *         @OA\Schema(type="string", format="uuid", example="019a4b7b-3481-738a-a2ff-d93fc45bac01")
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
     * Set primary photo
     *
     * @OA\Put(
     *     path="/accommodation-drafts/{draftId}/photos/{photoId}/primary",
     *     operationId="setPrimaryAccommodationDraftPhoto",
     *     tags={"Accommodation Draft Photo"},
     *     summary="Set photo as primary",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Primary photo set successfully")
     * )
     */
    public function setPrimary(
        AccommodationDraft $accommodationDraft,
        AccommodationDraftPhoto $photo
    ): JsonResponse {
        try {
            // Verify photo belongs to draft
            if ($photo->accommodation_draft_id !== $accommodationDraft->id) {
                return ApiResponse::error('Photo not found.', null, null, 404);
            }

            $success = $this->photoService->setPrimaryPhoto(
                $accommodationDraft,
                $photo->id
            );

            if (!$success) {
                return ApiResponse::error('Failed to set primary photo.', null, null, 500);
            }

            $photos = $accommodationDraft->photos()
                ->ordered()
                ->get();

            return ApiResponse::success(
                'Primary photo set successfully.',
                AccommodationDraftPhotoResource::collection($photos)
            );
        } catch (Exception $e) {
            Log::error('Failed to set primary photo', [
                'draft_id' => $accommodationDraft->id,
                'photo_id' => $photo->id,
                'error' => $e->getMessage(),
            ]);

            return ApiResponse::error('Failed to set primary photo.', null, null, 500);
        }
    }

    /**
     * Update photo details
     *
     * @OA\Put(
     *     path="/accommodation-drafts/{draftId}/photos/{photoId}",
     *     operationId="updateAccommodationDraftPhoto",
     *     tags={"Accommodation Draft Photo"},
     *     summary="Update photo details",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Photo updated successfully")
     * )
     */
    public function update(
        Request $request,
        AccommodationDraft $accommodationDraft,
        AccommodationDraftPhoto $photo
    ): JsonResponse {
        try {
            // Verify photo belongs to draft
            if ($photo->accommodation_draft_id !== $accommodationDraft->id) {
                return ApiResponse::error('Photo not found.', null, null, 404);
            }

            $validated = $request->validate([
                'alt_text' => 'nullable|string|max:255',
                'caption' => 'nullable|string|max:500',
                'is_primary' => 'nullable|boolean',
            ]);

            // If setting as primary, use service method
            if (isset($validated['is_primary']) && $validated['is_primary']) {
                $this->photoService->setPrimaryPhoto($accommodationDraft, $photo->id);
                unset($validated['is_primary']);
            }

            // Update remaining fields
            if (!empty($validated)) {
                $photo->update($validated);
            }

            return ApiResponse::success(
                'Photo updated successfully.',
                new AccommodationDraftPhotoResource($photo->fresh())
            );
        } catch (Exception $e) {
            Log::error('Failed to update photo', [
                'photo_id' => $photo->id,
                'error' => $e->getMessage(),
            ]);

            return ApiResponse::error('Failed to update photo.', null, null, 500);
        }
    }

    /**
     * Delete a photo
     *
     * @OA\Delete(
     *     path="/accommodation-drafts/{draftId}/photos/{photoId}",
     *     operationId="deleteAccommodationDraftPhoto",
     *     tags={"Accommodation Draft Photo"},
     *     summary="Delete a photo",
     *     security={{"bearerAuth":{}}},
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
     * Delete all photos for a draft
     *
     * @OA\Delete(
     *     path="/accommodation-drafts/{id}/photos",
     *     operationId="deleteAllAccommodationDraftPhotos",
     *     tags={"Accommodation Draft Photo"},
     *     summary="Delete all photos",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="All photos deleted successfully")
     * )
     */
    public function destroyAll(AccommodationDraft $accommodationDraft): JsonResponse
    {
        try {
            $success = $this->photoService->deleteAllPhotos($accommodationDraft);

            if (!$success) {
                return ApiResponse::error('Failed to delete all photos.', null, null, 500);
            }

            Log::info('All photos deleted', [
                'draft_id' => $accommodationDraft->id,
            ]);

            return ApiResponse::success('All photos deleted successfully.', null);
        } catch (Exception $e) {
            Log::error('Failed to delete all photos', [
                'draft_id' => $accommodationDraft->id,
                'error' => $e->getMessage(),
            ]);

            return ApiResponse::error('Failed to delete photos.', null, null, 500);
        }
    }

    /**
     * Get photo statistics
     *
     * @OA\Get(
     *     path="/accommodation-drafts/{id}/photos/stats",
     *     operationId="getAccommodationDraftPhotoStats",
     *     tags={"Accommodation Draft Photo"},
     *     summary="Get photo statistics",
     *     security={{"bearerAuth":{}}},
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
