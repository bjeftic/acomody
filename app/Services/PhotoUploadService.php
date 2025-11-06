<?php

namespace App\Services;

use App\Models\AccommodationDraft;
use App\Models\AccommodationDraftPhoto;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Photo Upload Service for Accommodation Drafts
 * Uses new storage bucket system with automatic lifecycle management
 */
class PhotoUploadService
{
    protected ImageUploadService $imageService;

    public function __construct(ImageUploadService $imageService)
    {
        // Configure image service for draft photos
        // Uses 'accommodation_draft_photos' bucket (auto-deleted after 7 days)
        $this->imageService = $imageService->setDisk('accommodation_draft_photos');
    }

    /**
     * Upload and process a photo for accommodation draft
     */
    public function uploadDraftPhoto(
        AccommodationDraft $draft,
        UploadedFile $file,
        int $order = 0,
        bool $isPrimary = false
    ): AccommodationDraftPhoto {
        DB::beginTransaction();

        try {
            // Create photo record with pending status
            $photo = AccommodationDraftPhoto::create([
                'accommodation_draft_id' => $draft->id,
                'original_filename' => $file->getClientOriginalName(),
                'path' => '', // to be updated after upload
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'order' => $order,
                'is_primary' => $isPrimary,
                'status' => 'pending',
                'uploaded_at' => now(),
            ]);

            // Upload using image service
            // Folder structure: draft-{id}/
            $uploadResult = $this->imageService->upload(
                $file,
                "draft-{$draft->id}",
                [
                    'extract_metadata' => true,
                ]
            );

            // Update photo record with paths and metadata
            $photo->update([
                'path' => $uploadResult['original_path'],
                'thumbnail_path' => $uploadResult['resized_paths']['thumbnail'] ?? null,
                'medium_path' => $uploadResult['resized_paths']['medium'] ?? null,
                'large_path' => $uploadResult['resized_paths']['large'] ?? null,
                'width' => $uploadResult['dimensions']['width'],
                'height' => $uploadResult['dimensions']['height'],
                'metadata' => $uploadResult['metadata'],
                'status' => 'completed',
                'processed_at' => now(),
            ]);

            DB::commit();

            Log::info('Draft photo uploaded successfully', [
                'photo_id' => $photo->id,
                'draft_id' => $draft->id,
                'filename' => $file->getClientOriginalName(),
                'disk' => 'accommodation_draft_photos',
            ]);

            return $photo->fresh();
        } catch (Exception $e) {
            DB::rollBack();

            // Update photo status if record was created
            if (isset($photo) && $photo->exists) {
                $photo->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }

            Log::error('Draft photo upload failed', [
                'draft_id' => $draft->id,
                'filename' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Upload multiple photos in batch
     */
    public function uploadMultiplePhotos(
        AccommodationDraft $draft,
        array $files,
        int $startOrder = 0
    ): array {
        $uploadedPhotos = [];
        $failedPhotos = [];
        $order = $startOrder;

        // Determine if first photo should be primary
        $hasPrimaryPhoto = $draft->photos()->where('is_primary', true)->exists();

        foreach ($files as $file) {
            try {
                $isPrimary = !$hasPrimaryPhoto && empty($uploadedPhotos);

                $photo = $this->uploadDraftPhoto($draft, $file, $order, $isPrimary);
                $uploadedPhotos[] = $photo;
                $order++;
            } catch (Exception $e) {
                $failedPhotos[] = [
                    'filename' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ];

                Log::warning('Failed to upload photo in batch', [
                    'draft_id' => $draft->id,
                    'filename' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (!empty($failedPhotos)) {
            Log::warning('Some photos failed to upload in batch', [
                'draft_id' => $draft->id,
                'total_files' => count($files),
                'uploaded' => count($uploadedPhotos),
                'failed' => count($failedPhotos),
                'failed_files' => $failedPhotos,
            ]);
        }

        return [
            'uploaded' => $uploadedPhotos,
            'failed' => $failedPhotos,
            'total' => count($files),
            'success_count' => count($uploadedPhotos),
            'failed_count' => count($failedPhotos),
        ];
    }

    /**
     * Reorder photos and update primary status
     */
    public function reorderPhotos(AccommodationDraft $draft, array $photoIds): bool
    {
        DB::beginTransaction();

        try {
            foreach ($photoIds as $order => $photoId) {
                $draft->photos()
                    ->where('id', $photoId)
                    ->update([
                        'order' => $order,
                        'is_primary' => ($order === 0),
                    ]);
            }

            DB::commit();

            Log::info('Draft photos reordered', [
                'draft_id' => $draft->id,
                'photo_count' => count($photoIds),
            ]);

            return true;
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Failed to reorder draft photos', [
                'draft_id' => $draft->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Set photo as primary
     */
    public function setPrimaryPhoto(AccommodationDraft $draft, int $photoId): bool
    {
        DB::beginTransaction();

        try {
            // Remove primary flag from all photos
            $draft->photos()->update(['is_primary' => false]);

            // Set new primary photo
            $photo = $draft->photos()->findOrFail($photoId);
            $photo->update(['is_primary' => true]);

            DB::commit();

            Log::info('Primary photo updated', [
                'draft_id' => $draft->id,
                'photo_id' => $photoId,
            ]);

            return true;
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Failed to set primary photo', [
                'draft_id' => $draft->id,
                'photo_id' => $photoId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Delete a photo
     */
    public function deletePhoto(AccommodationDraftPhoto $photo): bool
    {
        DB::beginTransaction();

        try {
            $draftId = $photo->accommodation_draft_id;
            $wasPrimary = $photo->is_primary;

            // Delete files from storage
            $this->imageService->delete($photo->path, [
                $photo->thumbnail_path,
                $photo->medium_path,
                $photo->large_path,
            ]);

            // Delete database record (force delete to trigger file deletion)
            $photo->forceDelete();

            // If primary photo was deleted, set next photo as primary
            if ($wasPrimary) {
                $nextPhoto = AccommodationDraftPhoto::where('accommodation_draft_id', $draftId)
                    ->ordered()
                    ->first();

                if ($nextPhoto) {
                    $nextPhoto->update(['is_primary' => true]);
                }
            }

            DB::commit();

            Log::info('Draft photo deleted', [
                'photo_id' => $photo->id,
                'draft_id' => $draftId,
            ]);

            return true;
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Failed to delete draft photo', [
                'photo_id' => $photo->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Delete all photos for a draft
     */
    public function deleteAllPhotos(AccommodationDraft $draft): bool
    {
        DB::beginTransaction();

        try {
            $photos = $draft->photos;

            foreach ($photos as $photo) {
                $this->deletePhoto($photo);
            }

            DB::commit();

            Log::info('All draft photos deleted', [
                'draft_id' => $draft->id,
                'photo_count' => $photos->count(),
            ]);

            return true;
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Failed to delete all draft photos', [
                'draft_id' => $draft->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Migrate photos from draft to accommodation
     * Copies from 'accommodation_draft_photos' to 'accommodation_photos'
     */
    public function migratePhotosToAccommodation(
        AccommodationDraft $draft,
        $accommodation
    ): bool {
        DB::beginTransaction();

        try {
            $photos = $draft->photos()->completed()->ordered()->get();

            if ($photos->isEmpty()) {
                Log::warning('No photos to migrate', [
                    'draft_id' => $draft->id,
                ]);
                return true;
            }

            // Switch to accommodation_photos disk for permanent storage
            $targetService = (new ImageUploadService())->setDisk('accommodation_photos');

            foreach ($photos as $photo) {
                // Copy files from draft bucket to accommodation bucket
                $newPaths = [
                    'path' => $this->copyFileToPermanentStorage($photo->path, "property-{$accommodation->id}"),
                    'thumbnail_path' => $this->copyFileToPermanentStorage($photo->thumbnail_path, "property-{$accommodation->id}"),
                    'medium_path' => $this->copyFileToPermanentStorage($photo->medium_path, "property-{$accommodation->id}"),
                    'large_path' => $this->copyFileToPermanentStorage($photo->large_path, "property-{$accommodation->id}"),
                ];

                // Create photo in accommodation
                $accommodation->photos()->create([
                    'path' => $newPaths['path'],
                    'thumbnail_path' => $newPaths['thumbnail_path'],
                    'medium_path' => $newPaths['medium_path'],
                    'large_path' => $newPaths['large_path'],
                    'original_filename' => $photo->original_filename,
                    'mime_type' => $photo->mime_type,
                    'file_size' => $photo->file_size,
                    'width' => $photo->width,
                    'height' => $photo->height,
                    'order' => $photo->order,
                    'is_primary' => $photo->is_primary,
                    'metadata' => $photo->metadata,
                    'alt_text' => $photo->alt_text,
                    'caption' => $photo->caption,
                ]);
            }

            DB::commit();

            Log::info('Photos migrated to accommodation', [
                'draft_id' => $draft->id,
                'accommodation_id' => $accommodation->id,
                'photo_count' => $photos->count(),
            ]);

            return true;
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Failed to migrate photos', [
                'draft_id' => $draft->id,
                'accommodation_id' => $accommodation->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Copy file from draft storage to permanent accommodation storage
     */
    protected function copyFileToPermanentStorage(?string $sourcePath, string $targetFolder): ?string
    {
        if (!$sourcePath) {
            return null;
        }

        try {
            // Extract size prefix and filename from source path
            // draft-123/thumbnail/uuid.jpg -> property-456/thumbnail/uuid.jpg
            $pathParts = explode('/', $sourcePath);
            $size = $pathParts[1] ?? 'original'; // thumbnail, medium, large, original
            $filename = $pathParts[2] ?? basename($sourcePath);

            $targetPath = "{$targetFolder}/{$size}/{$filename}";

            // Copy from draft bucket to accommodation bucket
            return $this->imageService->copyToDisk(
                $sourcePath,
                'accommodation_photos',
                $targetPath
            ) ? $targetPath : null;
        } catch (Exception $e) {
            Log::error('Failed to copy file to permanent storage', [
                'source_path' => $sourcePath,
                'target_folder' => $targetFolder,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get photo statistics for a draft
     */
    public function getPhotoStats(AccommodationDraft $draft): array
    {
        $photos = $draft->photos;

        return [
            'total' => $photos->count(),
            'completed' => $photos->where('status', 'completed')->count(),
            'pending' => $photos->where('status', 'pending')->count(),
            'failed' => $photos->where('status', 'failed')->count(),
            'has_primary' => $photos->where('is_primary', true)->count() > 0,
            'total_size' => $photos->sum('file_size'),
            'total_size_formatted' => $this->formatBytes($photos->sum('file_size')),
        ];
    }

    /**
     * Format bytes to human-readable size
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }
}
