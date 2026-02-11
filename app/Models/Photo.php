<?php

namespace App\Models;

use App\Traits\HasStorageFiles;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Photo extends Model
{
    use HasFactory, SoftDeletes, HasUlids, HasStorageFiles;

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
        'metadata',
        'alt_text',
        'caption',
        'uploaded_at',
        'processed_at',
        'error_message',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_primary' => 'boolean',
        'uploaded_at' => 'datetime',
        'processed_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the parent photoable model (AccommodationDraft, Accommodation, etc.)
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
     * Get main photo URL (original)
     */
    public function getUrlAttribute(): ?string
    {
        if (!$this->path) {
            return null;
        }

        return $this->getSmartUrl($this->path);
    }

    /**
     * Get thumbnail URL
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->attributes['thumbnail_path']) {
            return $this->url;
        }

        return $this->getSmartUrl($this->attributes['thumbnail_path']);
    }

    /**
     * Get medium size URL
     */
    public function getMediumUrlAttribute(): ?string
    {
        if (!$this->attributes['medium_path']) {
            return $this->url;
        }

        return $this->getSmartUrl($this->attributes['medium_path']);
    }

    /**
     * Get large size URL
     */
    public function getLargeUrlAttribute(): ?string
    {
        if (!$this->attributes['large_path']) {
            return $this->url;
        }

        return $this->getSmartUrl($this->attributes['large_path']);
    }

    /**
     * Get formatted file size
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;

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
    protected function getSmartUrl(string $path): string
    {
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

        // Fix MinIO internal hostname
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
        $disk = $this->disk;

        // Get MinIO configuration
        $endpoint = config("filesystems.disks.{$disk}.endpoint");
        $publicUrl = config("filesystems.disks.{$disk}.url");

        if (!$endpoint || !$publicUrl) {
            return $url;
        }

        // Extract internal hostname from endpoint
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

        // Extract public hostname
        $publicHost = parse_url($publicUrl, PHP_URL_HOST);
        $publicPort = parse_url($publicUrl, PHP_URL_PORT);
        $publicScheme = parse_url($publicUrl, PHP_URL_SCHEME) ?? 'http';

        if (!$publicHost) {
            return $url;
        }

        // Build public URL
        $publicUrlBase = $publicScheme . '://' . $publicHost;
        if ($publicPort) {
            $publicUrlBase .= ':' . $publicPort;
        }

        // Replace internal URL with public URL
        return str_replace($internalUrl, $publicUrlBase, $url);
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

        if (empty($paths)) {
            return true;
        }

        try {
            $storage = Storage::disk($this->disk);

            foreach ($paths as $path) {
                if ($storage->exists($path)) {
                    $storage->delete($path);
                }
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to delete photo files', [
                'photo_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Boot method - Auto-delete files on force delete
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($photo) {
            // Only delete files on force delete (permanent deletion)
            if (!$photo->isForceDeleting()) {
                return;
            }

            $photo->deleteAllFiles();
        });
    }
}
