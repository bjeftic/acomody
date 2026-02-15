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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
            if (!Storage::disk('temp')->exists($this->tempFilePath)) {
                throw new Exception("Temporary file not found: {$this->tempFilePath}");
            }

            // Get the model instance
            $model = $this->modelType::findOrFail($this->modelId);

            // Determine disk based on model type
            $disk = $this->getDiskForModel($model);
            $imageService->setDisk($disk);

            Log::info('IMAGE SERVICE DISK SET', [
                'disk' => $disk,
                'service_disk' => $imageService->getDisk(),
                'disk_driver' => config("filesystems.disks.$disk.driver"),
                'disk_bucket' => config("filesystems.disks.$disk.bucket"),
            ]);

            // Download file from MinIO temp to local temp
            $tempContent = Storage::disk('temp')->get($this->tempFilePath);
            $localTempPath = sys_get_temp_dir() . '/' . basename($this->tempFilePath);
            file_put_contents($localTempPath, $tempContent);

            // Create UploadedFile instance from local temp file
            $uploadedFile = new UploadedFile(
                $localTempPath,
                $this->originalFilename,
                $this->mimeType,
                null,
                true // test mode - skip mime type validation as file is already validated
            );

            // Upload and process image
            $folderName = $this->getFolderName($model);

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
            Storage::disk('temp')->delete($this->tempFilePath);

            @unlink($localTempPath);

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

            // Clean up temp file on failure
            try {
                Storage::disk('temp')->delete($this->tempFilePath);
            } catch (Exception $cleanupException) {
                Log::warning('Failed to cleanup temp file', [
                    'temp_path' => $this->tempFilePath,
                    'error' => $cleanupException->getMessage(),
                ]);
            }

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
            Storage::disk('temp')->delete($this->tempFilePath);
        } catch (Exception $e) {
            Log::warning('Failed to cleanup temp file in failed handler', [
                'temp_path' => $this->tempFilePath,
            ]);
        }
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
