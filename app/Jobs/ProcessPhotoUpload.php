<?php

namespace App\Jobs;

use App\Events\PhotoUploadCompleted;
use App\Events\PhotoUploadFailed;
use App\Models\Photo;
use App\Services\ImageUploadService;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessPhotoUpload implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 120;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public array $backoff = [10, 30, 60];

    /**
     * Delete the job if its models no longer exist.
     */
    public bool $deleteWhenMissingModels = true;

    protected string $photoId;

    protected string $tempFilePath;

    protected string $originalFilename;

    protected string $mimeType;

    protected int $fileSize;

    protected string $modelType;

    protected string $modelId;

    protected int $order;

    protected bool $isPrimary;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $photoId,
        string $tempFilePath,
        string $originalFilename,
        string $mimeType,
        int $fileSize,
        string $modelType,
        string $modelId,
        int $order,
        bool $isPrimary = false
    ) {
        $this->photoId = $photoId;
        $this->tempFilePath = $tempFilePath;
        $this->originalFilename = $originalFilename;
        $this->mimeType = $mimeType;
        $this->fileSize = $fileSize;
        $this->modelType = $modelType;
        $this->modelId = $modelId;
        $this->order = $order;
        $this->isPrimary = $isPrimary;

        // Use dedicated queue for photo processing
        $this->onQueue('photo-processing');
    }

    /**
     * Execute the job.
     */
    public function handle(ImageUploadService $imageService): void
    {
        // Check if batch has been cancelled
        if ($this->batch()?->cancelled()) {
            Log::info('Photo upload job cancelled (batch cancelled)', [
                'photo_id' => $this->photoId,
            ]);

            return;
        }

        DB::beginTransaction();

        try {
            // Find the photo record
            $photo = Photo::findOrFail($this->photoId);

            // Verify temp file exists
            $tempFilePath = storage_path('app/private/'.$this->tempFilePath);

            if (! file_exists($tempFilePath)) {
                throw new Exception("Temporary file not found: {$this->tempFilePath}");
            }

            // Determine disk and folder from stored model type/id (no model load needed)
            $disk = $this->getDiskForModelType($this->modelType);
            $imageService->setDisk($disk);

            // Create UploadedFile instance from temp file
            $uploadedFile = new UploadedFile(
                $tempFilePath,
                $this->originalFilename,
                $this->mimeType,
                null,
                true // test mode - skip mime type validation as file is already validated
            );

            // Upload and process image
            $folderName = $this->getFolderNameForModelType($this->modelType, $this->modelId);

            $uploadResult = $imageService->upload(
                $uploadedFile,
                $folderName,
                ['extract_metadata' => true]
            );

            // Update photo record
            $photo->update([
                'disk' => $disk,
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

            // Clean up temp file
            @unlink($tempFilePath);

            Log::info('Photo processed successfully', [
                'photo_id' => $this->photoId,
                'model_type' => $this->modelType,
                'model_id' => $this->modelId,
            ]);

            // Broadcast success event for real-time updates
            broadcast(new PhotoUploadCompleted($photo))->toOthers();

        } catch (Exception $e) {
            DB::rollBack();

            // Update photo status
            Photo::where('id', $this->photoId)->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error('Photo processing failed', [
                'photo_id' => $this->photoId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Broadcast failure event
            broadcast(new PhotoUploadFailed($this->photoId, $e->getMessage()))->toOthers();

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Exception $exception): void
    {
        Log::error('Photo upload job failed permanently', [
            'photo_id' => $this->photoId,
            'model_type' => $this->modelType,
            'model_id' => $this->modelId,
            'error' => $exception?->getMessage(),
        ]);

        // Update photo record
        Photo::where('id', $this->photoId)->update([
            'status' => 'failed',
            'error_message' => $exception?->getMessage() ?? 'Unknown error',
        ]);

        // Broadcast failure
        broadcast(new PhotoUploadFailed($this->photoId, $exception?->getMessage()))->toOthers();

        // Cleanup
        try {
            @unlink(storage_path('app/private/'.$this->tempFilePath));
        } catch (Exception $e) {
            Log::warning('Failed to cleanup temp file in failed handler', [
                'temp_path' => $this->tempFilePath,
            ]);
        }
    }

    /**
     * Get disk for model type
     */
    protected function getDiskForModelType(string $modelClass): string
    {
        return match ($modelClass) {
            'App\\Models\\AccommodationDraft' => config('images.presets.accommodation_draft.disk'),
            'App\\Models\\Accommodation' => config('images.presets.accommodation.disk'),
            'App\\Models\\User' => config('images.presets.user_profile.disk'),
            default => 'accommodation_draft_photos',
        };
    }

    /**
     * Get folder name for model type
     */
    protected function getFolderNameForModelType(string $modelClass, string $modelId): string
    {
        return match ($modelClass) {
            'App\\Models\\AccommodationDraft' => "draft-{$modelId}",
            'App\\Models\\Accommodation' => "property-{$modelId}",
            'App\\Models\\User' => "user-{$modelId}",
            default => "unknown-{$modelId}",
        };
    }
}
