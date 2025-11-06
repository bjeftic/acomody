<?php

namespace App\Models;

use App\Traits\HasStorageFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AccommodationDraftPhoto extends Model
{
    use HasFactory, SoftDeletes, HasStorageFiles;

    /**
     * Storage disk for draft photos
     */
    protected $storageDisk = 'accommodation_draft_photos';

    protected $fillable = [
        'accommodation_draft_id',
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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = [
        'url',
        'thumbnail_url',
        'medium_url',
        'large_url',
        'formatted_size',
    ];

    /**
     * Relationships
     */
    public function accommodationDraft(): BelongsTo
    {
        return $this->belongsTo(AccommodationDraft::class);
    }

    /**
     * Accessors - Smart URL generation (public or signed based on bucket visibility)
     */
    public function getUrlAttribute(): ?string
    {
        if (!$this->path) {
            return null;
        }

        return $this->getSmartUrl($this->path);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail_path) {
            return $this->url;
        }

        return $this->getSmartUrl($this->thumbnail_path);
    }

    public function getMediumUrlAttribute(): ?string
    {
        if (!$this->medium_path) {
            return $this->url;
        }

        return $this->getSmartUrl($this->medium_path);
    }

    public function getLargeUrlAttribute(): ?string
    {
        if (!$this->large_path) {
            return $this->url;
        }

        return $this->getSmartUrl($this->large_path);
    }

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
        $storage = Storage::disk($this->storageDisk);

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
        $visibility = config("filesystems.disks.{$this->storageDisk}.visibility");

        return $visibility === 'public';
    }

    /**
     * Get signed URL with custom expiration (force signature even for public buckets)
     */
    public function getSignedUrl(string $pathField = 'path', int $hours = 24): ?string
    {
        $path = $this->$pathField;

        if (!$path) {
            return null;
        }

        try {
            $storage = Storage::disk($this->storageDisk);
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
        $path = $this->$pathField;

        if (!$path) {
            return null;
        }

        $storage = Storage::disk($this->storageDisk);
        $url = $storage->url($path);

        return $this->fixMinioUrl($url);
    }

    /**
     * Fix MinIO URL - replace internal hostname with public URL
     */
    protected function fixMinioUrl(string $url): string
    {
        $disk = $this->storageDisk;

        // Get MinIO configuration
        $endpoint = config("filesystems.disks.{$disk}.endpoint", 'http://minio:9000');
        $publicUrl = config("filesystems.disks.{$disk}.url");

        // If MINIO_URL is not set, use APP_URL with port 9000
        if (!$publicUrl) {
            $publicUrl = config('app.url') . ':9000';
        }

        // Extract internal hostname from endpoint
        $internalHost = parse_url($endpoint, PHP_URL_HOST);
        $internalPort = parse_url($endpoint, PHP_URL_PORT);
        $internalScheme = parse_url($endpoint, PHP_URL_SCHEME);

        // Build internal URL pattern
        $internalUrl = $internalScheme . '://' . $internalHost;
        if ($internalPort) {
            $internalUrl .= ':' . $internalPort;
        }

        // Extract public hostname
        $publicHost = parse_url($publicUrl, PHP_URL_HOST);
        $publicPort = parse_url($publicUrl, PHP_URL_PORT);
        $publicScheme = parse_url($publicUrl, PHP_URL_SCHEME) ?? 'http';

        // Build public URL
        $publicUrlBase = $publicScheme . '://' . $publicHost;
        if ($publicPort) {
            $publicUrlBase .= ':' . $publicPort;
        }

        // Replace internal URL with public URL
        return str_replace($internalUrl, $publicUrlBase, $url);
    }

    /**
     * Helper Methods
     */
    public function deleteAllFiles(): bool
    {
        $paths = array_filter([
            $this->path,
            $this->thumbnail_path,
            $this->medium_path,
            $this->large_path,
        ]);

        if (empty($paths)) {
            return true;
        }

        return $this->deleteFiles($paths);
    }

    /**
     * Scopes
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeForDraft($query, int $draftId)
    {
        return $query->where('accommodation_draft_id', $draftId);
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
