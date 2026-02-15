<?php

namespace App\Services;

use App\Jobs\ProcessPhotoUpload;
use App\Models\Photo;
use Exception;
use Illuminate\Bus\Batch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Universal Photo Service for handling polymorphic photo relationships
 * Supports both sync and async (queued) uploads
 */
class PhotoService
{
    protected ImageUploadService $imageService;

    public function __construct(ImageUploadService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Upload and process a photo synchronously (old method - kept for backwards compatibility)
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
            $folderName = $this->getFolderName($model);

            $uploadResult = $this->imageService->upload(
                $file,
                $folderName,
                ['extract_metadata' => true]
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

            Log::info('Photo uploaded successfully (sync)', [
                'photo_id' => $photo->id,
                'model_type' => get_class($model),
                'model_id' => $model->id,
            ]);

            return $photo->fresh();
        } catch (Exception $e) {
            DB::rollBack();

            if (isset($photo) && $photo->exists) {
                $photo->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }

            Log::error('Photo upload failed (sync)', [
                'model_type' => get_class($model),
                'model_id' => $model->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Queue multiple photo uploads as a batch (RECOMMENDED METHOD)
     *
     * @param Model $model
     * @param array $files Array of UploadedFile instances
     * @param int $startOrder Starting order number
     * @return array ['batch_id' => string, 'photos' => Photo[], 'total' => int]
     */
    public function queuePhotoUploads(
        Model $model,
        array $files,
        int $startOrder = 0
    ): array {
        $photos = [];
        $jobs = [];
        $order = $startOrder;

        // Determine if first photo should be primary
        $hasPrimaryPhoto = $model->photos()->where('is_primary', true)->exists();

        DB::beginTransaction();

        try {
            foreach ($files as $file) {
                // Validate file before queueing
                $this->validateFile($file);

                $isPrimary = !$hasPrimaryPhoto && empty($photos);

                // Create Photo record with 'pending' status
                $photo = Photo::create([
                    'photoable_type' => get_class($model),
                    'photoable_id' => $model->id,
                    'disk' => $this->getDiskForModel($model),
                    'original_filename' => $file->getClientOriginalName(),
                    'path' => '', // Will be filled by job
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'order' => $order,
                    'is_primary' => $isPrimary,
                    'status' => 'pending',
                    'uploaded_at' => now(),
                ]);

                // Store file temporarily
                $tempPath = $this->storeTemporaryFile($file);

                // Create job for this photo
                $jobs[] = new ProcessPhotoUpload(
                    photoId: $photo->id,
                    tempFilePath: $tempPath,
                    originalFilename: $file->getClientOriginalName(),
                    mimeType: $file->getMimeType(),
                    fileSize: $file->getSize(),
                    modelType: get_class($model),
                    modelId: $model->id,
                    order: $order,
                    isPrimary: $isPrimary
                );

                $photos[] = $photo;
                $order++;
            }

            DB::commit();

            // Dispatch batch
            $batch = Bus::batch($jobs)
                ->name("photo-upload-{$model->id}")
                ->allowFailures() // Don't cancel entire batch if one photo fails
                ->onQueue('photo-processing')
                ->dispatch();

            Log::info('Photo upload batch dispatched', [
                'batch_id' => $batch->id,
                'model_type' => get_class($model),
                'model_id' => $model->id,
                'photo_count' => count($photos),
            ]);

            return [
                'batch_id' => $batch->id,
                'photos' => $photos,
                'total' => count($photos),
            ];

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Failed to queue photo uploads', [
                'model_type' => get_class($model),
                'model_id' => $model->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Upload multiple photos synchronously (kept for backwards compatibility)
     */
    public function uploadMultiplePhotos(
        Model $model,
        array $files,
        int $startOrder = 0
    ): array {
        $uploadedPhotos = [];
        $failedPhotos = [];
        $order = $startOrder;

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

        return [
            'uploaded' => $uploadedPhotos,
            'failed' => $failedPhotos,
            'total' => count($files),
            'success_count' => count($uploadedPhotos),
            'failed_count' => count($failedPhotos),
        ];
    }

    /**
     * Store file temporarily for queue processing
     */
    protected function storeTemporaryFile(UploadedFile $file): string
{
    Log::info('TEMP FILE UPLOAD START', [
        'filename' => $file->getClientOriginalName(),
        'size' => $file->getSize(),
    ]);

    $filename = Str::ulid() . '.' . $file->getClientOriginalExtension();
    $path = "uploads/{$filename}";

    Log::info('TEMP FILE UPLOADING', [
        'path' => $path,
        'disk' => 'temp',
    ]);

    $result = Storage::disk('temp')->put($path, file_get_contents($file->getRealPath()));

    Log::info('TEMP FILE UPLOADED', [
        'path' => $path,
        'result' => $result,
        'exists' => Storage::disk('temp')->exists($path),
    ]);

    return $path;
}

    /**
     * Validate uploaded file
     */
    protected function validateFile(UploadedFile $file): void
    {
        $maxFileSize = config('images.max_file_size', 10485760);
        $allowedMimeTypes = config('images.allowed_mime_types', [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/webp',
        ]);

        if (!$file->isValid()) {
            throw new Exception('Invalid file upload');
        }

        if ($file->getSize() > $maxFileSize) {
            $maxSizeMB = round($maxFileSize / 1048576, 2);
            throw new Exception("File size exceeds maximum allowed size of {$maxSizeMB}MB");
        }

        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            throw new Exception('Invalid file type. Only JPEG, PNG, and WebP images are allowed');
        }
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
            $model->photos()->update(['is_primary' => false]);
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
            $disk = $photo->disk;

            // Delete all image files
            $pathsToDelete = array_filter([
                $photo->path,
                $photo->thumbnail_path,
                $photo->medium_path,
                $photo->large_path,
            ]);

            foreach ($pathsToDelete as $path) {
                try {
                    Storage::disk($disk)->delete($path);
                } catch (Exception $e) {
                    Log::warning('Failed to delete photo file', [
                        'photo_id' => $photo->id,
                        'path' => $path,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $photo->delete();

            Log::info('Photo deleted', [
                'photo_id' => $photo->id,
                'photoable_type' => $photo->photoable_type,
                'photoable_id' => $photo->photoable_id,
            ]);
        });
    }

    /**
     * Get disk for model
     */
    protected function getDiskForModel(Model $model): string
    {
        $modelClass = get_class($model);

        return match($modelClass) {
            'App\\Models\\AccommodationDraft' => config('images.presets.accommodation_draft.disk'),
            'App\\Models\\Accommodation' => config('images.presets.accommodation.disk'),
            'App\\Models\\User' => config('images.presets.user_profile.disk'),
            default => 'accommodation_draft_photos',
        };
    }

    /**
     * Get folder name for model
     */
    protected function getFolderName(Model $model): string
    {
        $modelClass = get_class($model);

        return match($modelClass) {
            'App\\Models\\AccommodationDraft' => "draft-{$model->id}",
            'App\\Models\\Accommodation' => "property-{$model->id}",
            'App\\Models\\User' => "user-{$model->id}",
            default => "unknown-{$model->id}",
        };
    }
}
