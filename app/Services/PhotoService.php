<?php

namespace App\Services;

use App\Models\Photo;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Universal Photo Service for handling polymorphic photo relationships
 * Supports both draft and permanent photos through single Photo model
 */
class PhotoService
{
    protected ImageUploadService $imageService;

    public function __construct(ImageUploadService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Upload and process a photo for any model
     */
    public function uploadPhoto(
        Model $model,
        UploadedFile $file,
        int $order = 0,
        bool $isPrimary = false
    ): Photo {
        DB::beginTransaction();

        try {
            // Determine disk based on model type
            $disk = $this->getDiskForModel($model);
            $this->imageService->setDisk($disk);

            // Create photo record with pending status
            $photo = Photo::create([
                'photoable_type' => get_class($model),
                'photoable_id' => $model->id,
                'disk' => $disk,
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
            // Folder structure: {prefix}-{id}/ (e.g., draft-123/ or property-456/)
            $folderName = $this->getFolderName($model);

            $uploadResult = $this->imageService->upload(
                $file,
                $folderName,
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

            Log::info('Photo uploaded successfully', [
                'photo_id' => $photo->id,
                'model_type' => get_class($model),
                'model_id' => $model->id,
                'filename' => $file->getClientOriginalName(),
                'disk' => $disk,
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

            Log::error('Photo upload failed', [
                'model_type' => get_class($model),
                'model_id' => $model->id,
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
        Model $model,
        array $files,
        int $startOrder = 0
    ): array {
        $uploadedPhotos = [];
        $failedPhotos = [];
        $order = $startOrder;

        // Determine if first photo should be primary
        $hasPrimaryPhoto = $model->photos()->where('is_primary', true)->exists();

        foreach ($files as $file) {
            try {
                $isPrimary = !$hasPrimaryPhoto && empty($uploadedPhotos);

                $photo = $this->uploadPhoto($model, $file, $order, $isPrimary);
                $uploadedPhotos[] = $photo;
                $order++;
            } catch (Exception $e) {
                $failedPhotos[] = [
                    'filename' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ];

                Log::warning('Failed to upload photo in batch', [
                    'model_type' => get_class($model),
                    'model_id' => $model->id,
                    'filename' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (!empty($failedPhotos)) {
            Log::warning('Some photos failed to upload in batch', [
                'model_type' => get_class($model),
                'model_id' => $model->id,
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
     * Reorder photos
     */
    public function reorderPhotos(Model $model, array $photoIds): void
    {
        DB::transaction(function () use ($model, $photoIds) {
            foreach ($photoIds as $index => $photoId) {
                Photo::where('id', $photoId)
                    ->where('photoable_type', get_class($model))
                    ->where('photoable_id', $model->id)
                    ->update(['order' => $index]);
            }

            Log::info('Photos reordered', [
                'model_type' => get_class($model),
                'model_id' => $model->id,
                'photo_count' => count($photoIds),
            ]);
        });
    }

    /**
     * Set photo as primary
     */
    public function setPrimaryPhoto(Model $model, string $photoId): void
    {
        DB::transaction(function () use ($model, $photoId) {
            // Remove primary flag from all photos
            $model->photos()->update(['is_primary' => false]);

            // Set new primary photo
            $photo = $model->photos()->findOrFail($photoId);
            $photo->update(['is_primary' => true]);

            Log::info('Primary photo updated', [
                'model_type' => get_class($model),
                'model_id' => $model->id,
                'photo_id' => $photoId,
            ]);
        });
    }

    /**
     * Delete a photo
     */
    public function deletePhoto(Photo $photo): void
    {
        DB::transaction(function () use ($photo) {
            $modelId = $photo->photoable_id;
            $modelType = $photo->photoable_type;
            $wasPrimary = $photo->is_primary;

            // Delete files from storage
            $paths = array_filter([
                $photo->path,
                $photo->thumbnail_path,
                $photo->medium_path,
                $photo->large_path,
            ]);

            foreach ($paths as $path) {
                Storage::disk($photo->disk)->delete($path);
            }

            // Delete database record
            $photo->forceDelete();

            // If primary photo was deleted, set next photo as primary
            if ($wasPrimary) {
                $nextPhoto = Photo::where('photoable_type', $modelType)
                    ->where('photoable_id', $modelId)
                    ->orderBy('order')
                    ->first();

                if ($nextPhoto) {
                    $nextPhoto->update(['is_primary' => true]);
                }
            }

            Log::info('Photo deleted', [
                'photo_id' => $photo->id,
                'model_type' => $modelType,
                'model_id' => $modelId,
            ]);
        });
    }

    /**
     * Delete all photos for a model
     */
    public function deleteAllPhotos(Model $model): void
    {
        DB::transaction(function () use ($model) {
            $photos = $model->photos()->get();

            foreach ($photos as $photo) {
                $this->deletePhoto($photo);
            }

            Log::info('All photos deleted', [
                'model_type' => get_class($model),
                'model_id' => $model->id,
                'photo_count' => $photos->count(),
            ]);
        });
    }

    /**
     * Migrate photos from one model to another (e.g., draft to permanent)
     * Copies from source disk to target disk
     */
    public function migratePhotos(Model $sourceModel, Model $targetModel): void
    {
        DB::transaction(function () use ($sourceModel, $targetModel) {
            $photos = $sourceModel->photos()
                ->where('status', 'completed')
                ->orderBy('order')
                ->get();

            if ($photos->isEmpty()) {
                Log::warning('No photos to migrate', [
                    'source_model_type' => get_class($sourceModel),
                    'source_model_id' => $sourceModel->id,
                ]);
                return;
            }

            $sourceDisk = $this->getDiskForModel($sourceModel);
            $targetDisk = $this->getDiskForModel($targetModel);
            $sourceFolderName = $this->getFolderName($sourceModel);
            $targetFolderName = $this->getFolderName($targetModel);

            foreach ($photos as $photo) {
                // Copy files from source disk to target disk
                $newPaths = [
                    'path' => $this->copyFileBetweenDisks(
                        $photo->path,
                        $sourceDisk,
                        $targetDisk,
                        $sourceFolderName,
                        $targetFolderName
                    ),
                    'thumbnail_path' => $this->copyFileBetweenDisks(
                        $photo->thumbnail_path,
                        $sourceDisk,
                        $targetDisk,
                        $sourceFolderName,
                        $targetFolderName
                    ),
                    'medium_path' => $this->copyFileBetweenDisks(
                        $photo->medium_path,
                        $sourceDisk,
                        $targetDisk,
                        $sourceFolderName,
                        $targetFolderName
                    ),
                    'large_path' => $this->copyFileBetweenDisks(
                        $photo->large_path,
                        $sourceDisk,
                        $targetDisk,
                        $sourceFolderName,
                        $targetFolderName
                    ),
                ];

                // Create photo for target model
                Photo::create([
                    'photoable_type' => get_class($targetModel),
                    'photoable_id' => $targetModel->id,
                    'disk' => $targetDisk,
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
                    'status' => 'completed',
                    'uploaded_at' => $photo->uploaded_at,
                    'processed_at' => $photo->processed_at,
                ]);
            }

            Log::info('Photos migrated successfully', [
                'source_model_type' => get_class($sourceModel),
                'source_model_id' => $sourceModel->id,
                'target_model_type' => get_class($targetModel),
                'target_model_id' => $targetModel->id,
                'photo_count' => $photos->count(),
            ]);
        });
    }

    /**
     * Get photo statistics for a model
     */
    public function getPhotoStats(Model $model): array
    {
        $photos = $model->photos;

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
     * Determine storage disk based on model type
     */
    protected function getDiskForModel(Model $model): string
    {
        $modelClass = get_class($model);

        // Map model types to their storage disks
        $diskMap = [
            'App\Models\AccommodationDraft' => 'accommodation_draft_photos',
            'App\Models\Accommodation' => 'accommodation_photos',
            // Add more mappings as needed for future models
        ];

        return $diskMap[$modelClass] ?? 'minio';
    }

    /**
     * Get folder name for model
     */
    protected function getFolderName(Model $model): string
    {
        $modelClass = get_class($model);

        // Map model types to folder prefixes
        $folderMap = [
            'App\Models\AccommodationDraft' => 'draft',
            'App\Models\Accommodation' => 'property',
            // Add more mappings as needed
        ];

        $prefix = $folderMap[$modelClass] ?? 'model';

        return "{$prefix}-{$model->id}";
    }

    /**
     * Copy file between storage disks
     */
    protected function copyFileBetweenDisks(
        ?string $sourcePath,
        string $sourceDisk,
        string $targetDisk,
        string $sourceFolderName,
        string $targetFolderName
    ): ?string {
        if (!$sourcePath) {
            return null;
        }

        try {
            // Replace source folder with target folder in path
            $targetPath = str_replace($sourceFolderName, $targetFolderName, $sourcePath);

            // Get file contents from source
            $fileContents = Storage::disk($sourceDisk)->get($sourcePath);

            // Put to target
            Storage::disk($targetDisk)->put($targetPath, $fileContents);

            return $targetPath;
        } catch (Exception $e) {
            Log::error('Failed to copy file between disks', [
                'source_path' => $sourcePath,
                'source_disk' => $sourceDisk,
                'target_disk' => $targetDisk,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
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
