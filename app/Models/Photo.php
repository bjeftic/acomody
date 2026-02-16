<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Photo extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'photoable_type',
        'photoable_id',
        'disk',
        'path',
        'thumbnail_path',
        'medium_path',
        'large_path',
        'original_filename',
        'mime_type',
        'file_size',
        'width',
        'height',
        'order',
        'is_primary',
        'status',
        'alt_text',
        'caption',
        'metadata',
        'error_message',
        'uploaded_at',
        'processed_at',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'metadata' => 'array',
        'uploaded_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    protected $appends = ['url', 'urls'];

    /**
     * Polymorphic relationship
     */
    public function photoable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for completed photos
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for ordered photos
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Scope for primary photo
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Get main photo URL (original) - NULL SAFE
     */
    public function getUrlAttribute(): ?string
    {
        if (!isset($this->attributes['path']) || empty($this->attributes['path'])) {
            return null;
        }

        return $this->getSmartUrl($this->attributes['path']);
    }

    /**
     * Get all URLs (for API response) - NULL SAFE
     */
    public function getUrlsAttribute(): array
    {
        return [
            'original' => $this->url,
            'large' => $this->large_url,
            'medium' => $this->medium_url,
            'thumbnail' => $this->thumbnail_url,
        ];
    }

    /**
     * Get thumbnail URL - NULL SAFE
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (!isset($this->attributes['thumbnail_path']) || empty($this->attributes['thumbnail_path'])) {
            return $this->url; // Fallback to original
        }

        return $this->getSmartUrl($this->attributes['thumbnail_path']);
    }

    /**
     * Get medium size URL - NULL SAFE
     */
    public function getMediumUrlAttribute(): ?string
    {
        if (!isset($this->attributes['medium_path']) || empty($this->attributes['medium_path'])) {
            return $this->url; // Fallback to original
        }

        return $this->getSmartUrl($this->attributes['medium_path']);
    }

    /**
     * Get large size URL - NULL SAFE
     */
    public function getLargeUrlAttribute(): ?string
    {
        if (!isset($this->attributes['large_path']) || empty($this->attributes['large_path'])) {
            return $this->url; // Fallback to original
        }

        return $this->getSmartUrl($this->attributes['large_path']);
    }

    /**
     * Get formatted file size
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size ?? 0;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }

    /**
     * Smart URL generation - returns direct URL for public buckets, signed URL for private
     */
    protected function getSmartUrl(string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        // Handle seed data
        if (str_starts_with($path, 'seed/')) {
            return "https://picsum.photos/{$path}";
        }

        $storage = Storage::disk($this->disk);

        // Check if bucket is configured as public
        $isPublic = $this->isBucketPublic();

        if ($isPublic) {
            // Public bucket - use direct URL without signature
            $url = $storage->url($path);
        } else {
            // Private bucket - use signed URL (1 hour validity)
            try {
                $url = $storage->temporaryUrl($path, now()->addHour());
            } catch (\Exception $e) {
                // Fallback to regular URL if temporaryUrl fails
                $url = $storage->url($path);
            }
        }

        // Fix MinIO URL
        return $this->fixMinioUrl($url);
    }

    /**
     * Check if bucket is configured as public
     */
    protected function isBucketPublic(): bool
    {
        // Check visibility in disk configuration
        $visibility = config("filesystems.disks.{$this->disk}.visibility");

        return $visibility === 'public';
    }

    /**
     * Get signed URL with custom expiration (force signature even for public buckets)
     */
    public function getSignedUrl(string $pathField = 'path', int $hours = 24): ?string
    {
        $path = $this->attributes[$pathField] ?? null;

        if (!$path) {
            return null;
        }

        try {
            $storage = Storage::disk($this->disk);
            $url = $storage->temporaryUrl($path, now()->addHours($hours));
            return $this->fixMinioUrl($url);
        } catch (\Exception $e) {
            // Fallback to regular URL
            return $this->getSmartUrl($path);
        }
    }

    /**
     * Get direct URL without signature (force public access)
     */
    public function getDirectUrl(string $pathField = 'path'): ?string
    {
        $path = $this->attributes[$pathField] ?? null;

        if (!$path) {
            return null;
        }

        $storage = Storage::disk($this->disk);
        $url = $storage->url($path);

        return $this->fixMinioUrl($url);
    }

    /**
     * Fix MinIO URL - replace internal hostname with public URL
     */
    protected function fixMinioUrl(string $url): string
    {
        // Get disk configuration
        $endpoint = config("filesystems.disks.{$this->disk}.endpoint");
        $publicUrl = config("filesystems.disks.{$this->disk}.url");

        if (!$endpoint || !$publicUrl) {
            return $url;
        }

        // Parse internal endpoint (e.g., http://minio:9000)
        $internalHost = parse_url($endpoint, PHP_URL_HOST);
        $internalPort = parse_url($endpoint, PHP_URL_PORT);
        $internalScheme = parse_url($endpoint, PHP_URL_SCHEME);

        if (!$internalHost || !$internalScheme) {
            return $url;
        }

        // Build internal URL pattern
        $internalUrl = $internalScheme . '://' . $internalHost;
        if ($internalPort) {
            $internalUrl .= ':' . $internalPort;
        }

        // Parse public URL (e.g., http://localhost:9000)
        $publicHost = parse_url($publicUrl, PHP_URL_HOST);
        $publicPort = parse_url($publicUrl, PHP_URL_PORT);
        $publicScheme = parse_url($publicUrl, PHP_URL_SCHEME) ?? 'http';

        if (!$publicHost) {
            return $url;
        }

        // Build public URL base
        $publicUrlBase = $publicScheme . '://' . $publicHost;
        if ($publicPort) {
            $publicUrlBase .= ':' . $publicPort;
        }

        // Replace internal URL with public URL
        $fixedUrl = str_replace($internalUrl, $publicUrlBase, $url);

        return $fixedUrl;
    }

    /**
     * Delete all photo files from storage
     */
    public function deleteAllFiles(): bool
    {
        $paths = array_filter([
            $this->attributes['path'] ?? null,
            $this->attributes['thumbnail_path'] ?? null,
            $this->attributes['medium_path'] ?? null,
            $this->attributes['large_path'] ?? null,
        ]);

        $storage = Storage::disk($this->disk);
        $success = true;

        foreach ($paths as $path) {
            try {
                if ($storage->exists($path)) {
                    $storage->delete($path);
                }
            } catch (\Exception $e) {
                $success = false;
                \Log::warning('Failed to delete photo file', [
                    'photo_id' => $this->id,
                    'path' => $path,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $success;
    }

    /**
     * Boot method - auto delete files when model is deleted
     */
    protected static function booted(): void
    {
        static::deleting(function (Photo $photo) {
            $photo->deleteAllFiles();
        });
    }
}
