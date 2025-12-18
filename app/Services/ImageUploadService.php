<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

/**
 * Generic Image Upload Service
 * Uses new storage bucket system (accommodation_draft_photos, accommodation_photos, etc.)
 */
class ImageUploadService
{
    protected ImageManager $imageManager;
    protected string $disk;
    protected array $sizes;
    protected int $maxFileSize;
    protected array $allowedMimeTypes;

    public function __construct(
        ?string $disk = null,
        ?array $sizes = null,
        ?int $maxFileSize = null,
        ?array $allowedMimeTypes = null
    ) {
        $this->imageManager = new ImageManager(new Driver());

        // Use provided values or defaults
        $this->disk = $disk ?? 'accommodation_draft_photos';
        $this->sizes = $sizes ?? [
            'thumbnail' => ['width' => 300, 'height' => 300, 'quality' => 80, 'crop' => true],
            'medium' => ['width' => 800, 'height' => 600, 'quality' => 85, 'crop' => false],
            'large' => ['width' => 1920, 'height' => 1440, 'quality' => 90, 'crop' => false],
        ];
        $this->maxFileSize = $maxFileSize ?? 10485760; // 10MB
        $this->allowedMimeTypes = $allowedMimeTypes ?? [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/webp',
        ];
    }

    /**
     * Upload and process an image
     *
     * @param UploadedFile $file
     * @param string $folder Folder path (e.g., 'draft-123' or 'property-456')
     * @param array $options Additional options
     * @return array Returns paths for all generated sizes
     */
    public function upload(
        UploadedFile $file,
        string $folder,
        array $options = []
    ): array {
        try {
            // Validate file
            $this->validateFile($file);

            // Generate unique filename
            $filename = $options['filename'] ?? $this->generateFilename($file);
            $baseFilename = pathinfo($filename, PATHINFO_FILENAME);

            // Upload original
            $originalPath = $this->uploadOriginal($file, $folder, $filename);

            // Read image for processing
            $image = $this->imageManager->read($file->getRealPath());

            // Get dimensions
            $dimensions = [
                'width' => $image->width(),
                'height' => $image->height(),
            ];

            // Generate resized versions
            $resizedPaths = [];
            foreach ($this->sizes as $sizeName => $sizeConfig) {
                if ($options['skip_sizes'][$sizeName] ?? false) {
                    continue;
                }

                $resizedPaths[$sizeName] = $this->generateResizedImage(
                    $image,
                    $folder,
                    $sizeName,
                    $sizeConfig,
                    $baseFilename
                );
            }

            // Extract metadata if requested
            $metadata = [];
            if ($options['extract_metadata'] ?? true) {
                $metadata = $this->extractMetadata($image, $file);
            }

            return [
                'original_path' => $originalPath,
                'resized_paths' => $resizedPaths,
                'dimensions' => $dimensions,
                'metadata' => $metadata,
                'file_info' => [
                    'original_filename' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ],
            ];
        } catch (Exception $e) {
            Log::error('Image upload failed', [
                'disk' => $this->disk,
                'folder' => $folder,
                'filename' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Delete image and all its sizes
     */
    public function delete(string $originalPath, array $resizedPaths = []): bool
    {
        try {
            $storage = Storage::disk($this->disk);
            $deleted = true;

            // Delete original
            if ($originalPath && $storage->exists($originalPath)) {
                $deleted = $storage->delete($originalPath) && $deleted;
            }

            // Delete resized versions
            foreach ($resizedPaths as $path) {
                if ($path && $storage->exists($path)) {
                    $deleted = $storage->delete($path) && $deleted;
                }
            }

            return $deleted;
        } catch (Exception $e) {
            Log::error('Image deletion failed', [
                'disk' => $this->disk,
                'original_path' => $originalPath,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get URL for image (temporary signed URL for S3/MinIO)
     */
    public function getUrl(
        ?string $path,
        int $expirationHours = 1
    ): ?string {
        if (!$path) {
            return null;
        }

        $storage = Storage::disk($this->disk);

        // For MinIO/S3, always use temporary signed URLs
        if (in_array($this->disk, ['accommodation_draft_photos', 'accommodation_photos', 'minio', 's3'])) {
            try {
                return $storage->temporaryUrl($path, now()->addHours($expirationHours));
            } catch (Exception $e) {
                Log::warning('Could not generate temporary URL, falling back to regular URL', [
                    'disk' => $this->disk,
                    'path' => $path,
                    'error' => $e->getMessage(),
                ]);
                return $storage->url($path);
            }
        }

        return $storage->url($path);
    }

    /**
     * Validate uploaded file
     */
    protected function validateFile(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw new Exception('Invalid file upload');
        }

        if ($file->getSize() > $this->maxFileSize) {
            $maxSizeMB = round($this->maxFileSize / 1048576, 2);
            throw new Exception("File size exceeds maximum allowed size of {$maxSizeMB}MB");
        }

        if (!in_array($file->getMimeType(), $this->allowedMimeTypes)) {
            throw new Exception(
                'Invalid file type. Only JPEG, PNG, and WebP images are allowed'
            );
        }
    }

    /**
     * Upload original file
     */
    protected function uploadOriginal(
        UploadedFile $file,
        string $folder,
        string $filename
    ): string {
        $path = "{$folder}/original/{$filename}";

        Storage::disk($this->disk)->put($path, file_get_contents($file->getRealPath()));

        return $path;
    }

    /**
     * Generate resized image
     */
    protected function generateResizedImage(
        $image,
        string $folder,
        string $sizeName,
        array $config,
        string $baseFilename
    ): string {
        $resized = clone $image;

        // Apply resize based on configuration
        if ($config['crop'] ?? false) {
            // Crop to exact dimensions (for thumbnails)
            $resized->cover($config['width'], $config['height']);
        } else {
            // Scale maintaining aspect ratio
            $resized->scale(width: $config['width'], height: $config['height']);
        }

        // Encode with quality (always use JPEG for consistency)
        $encoded = $resized->toJpeg(quality: $config['quality']);

        // Generate path
        $filename = "{$baseFilename}.jpg";
        $path = "{$folder}/{$sizeName}/{$filename}";

        Storage::disk($this->disk)->put($path, (string) $encoded);

        return $path;
    }

    /**
     * Generate unique filename
     */
    protected function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        return Str::ulid() . '.' . $extension;
    }

    /**
     * Extract image metadata
     */
    protected function extractMetadata($image, UploadedFile $file): array
    {
        $metadata = [
            'width' => $image->width(),
            'height' => $image->height(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'aspect_ratio' => round($image->width() / $image->height(), 2),
        ];

        try {
            $exif = $image->exif();

            if ($exif) {
                $exifArray = $exif->toArray();
                $metadata['exif'] = [
                    'orientation' => $exifArray['Orientation'] ?? null,
                    'camera_make' => $exifArray['Make'] ?? null,
                    'camera_model' => $exifArray['Model'] ?? null,
                    'date_taken' => $exifArray['DateTime'] ?? null,
                    'exposure_time' => $exifArray['ExposureTime'] ?? null,
                    'f_number' => $exifArray['FNumber'] ?? null,
                    'iso' => $exifArray['ISOSpeedRatings'] ?? null,
                ];
            }
        } catch (Exception $e) {
            // EXIF extraction is optional
            Log::debug('Could not extract EXIF data', [
                'filename' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);
        }

        return $metadata;
    }

    /**
     * Get configured disk name
     */
    public function getDisk(): string
    {
        return $this->disk;
    }

    /**
     * Get configured sizes
     */
    public function getSizes(): array
    {
        return $this->sizes;
    }

    /**
     * Set custom sizes for this instance
     */
    public function setSizes(array $sizes): self
    {
        $this->sizes = $sizes;
        return $this;
    }

    /**
     * Set custom disk for this instance
     */
    public function setDisk(string $disk): self
    {
        $this->disk = $disk;
        return $this;
    }

    /**
     * Check if file exists in storage
     */
    public function exists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }

    /**
     * Get file size
     */
    public function getSize(string $path): int
    {
        return Storage::disk($this->disk)->size($path);
    }

    /**
     * Copy file to another disk
     */
    public function copyToDisk(string $sourcePath, string $targetDisk, string $targetPath): bool
    {
        try {
            $contents = Storage::disk($this->disk)->get($sourcePath);
            return Storage::disk($targetDisk)->put($targetPath, $contents);
        } catch (Exception $e) {
            Log::error('Failed to copy file to another disk', [
                'source_disk' => $this->disk,
                'target_disk' => $targetDisk,
                'source_path' => $sourcePath,
                'target_path' => $targetPath,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
