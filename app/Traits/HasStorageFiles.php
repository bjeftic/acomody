<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

/**
 * Trait HasStorageFiles
 *
 * Provides convenient methods for working with storage buckets in models.
 *
 * Usage:
 *
 * class Accommodation extends Model
 * {
 *     use HasStorageFiles;
 *
 *     protected $storageDisk = 'accommodation_photos';
 *
 *     public function uploadPhoto(UploadedFile $file): string
 *     {
 *         return $this->storeFile($file, "property-{$this->id}");
 *     }
 * }
 */
trait HasStorageFiles
{
    /**
     * Get the storage disk for this model
     *
     * @return string
     */
    public function getStorageDisk(): string
    {
        return $this->storageDisk ?? 'local';
    }

    /**
     * Set the storage disk
     *
     * @param string $disk
     * @return $this
     */
    public function setStorageDisk(string $disk): self
    {
        $this->storageDisk = $disk;
        return $this;
    }

    /**
     * Store a file to the configured disk
     *
     * @param UploadedFile|string $file
     * @param string|null $directory
     * @param string|null $name
     * @return string The stored file path
     */
    public function storeFile($file, ?string $directory = null, ?string $name = null): string
    {
        $disk = Storage::disk($this->getStorageDisk());

        if ($name) {
            $path = $directory ? "{$directory}/{$name}" : $name;
            return $disk->putFileAs($directory ?? '', $file, $name);
        }

        return $disk->put($directory ?? '', $file);
    }

    /**
     * Store file with public visibility
     *
     * @param UploadedFile|string $file
     * @param string|null $directory
     * @param string|null $name
     * @return string
     */
    public function storeFilePublic($file, ?string $directory = null, ?string $name = null): string
    {
        $disk = Storage::disk($this->getStorageDisk());

        if ($name) {
            $path = $disk->putFileAs($directory ?? '', $file, $name, 'public');
        } else {
            $path = $disk->put($directory ?? '', $file, 'public');
        }

        return $path;
    }

    /**
     * Store file with private visibility
     *
     * @param UploadedFile|string $file
     * @param string|null $directory
     * @param string|null $name
     * @return string
     */
    public function storeFilePrivate($file, ?string $directory = null, ?string $name = null): string
    {
        $disk = Storage::disk($this->getStorageDisk());

        if ($name) {
            $path = $disk->putFileAs($directory ?? '', $file, $name, 'private');
        } else {
            $path = $disk->put($directory ?? '', $file, 'private');
        }

        return $path;
    }

    /**
     * Get public URL for a stored file
     *
     * @param string $path
     * @return string
     */
    public function getFileUrl(string $path): string
    {
        return Storage::disk($this->getStorageDisk())->url($path);
    }

    /**
     * Generate a temporary URL for a private file
     *
     * @param string $path
     * @param \DateTimeInterface|int $expiration
     * @return string
     */
    public function getTemporaryFileUrl(string $path, $expiration = null): string
    {
        $expiration = $expiration ?? now()->addHours(1);

        return Storage::disk($this->getStorageDisk())
            ->temporaryUrl($path, $expiration);
    }

    /**
     * Delete a file from storage
     *
     * @param string $path
     * @return bool
     */
    public function deleteFile(string $path): bool
    {
        return Storage::disk($this->getStorageDisk())->delete($path);
    }

    /**
     * Delete multiple files from storage
     *
     * @param array $paths
     * @return bool
     */
    public function deleteFiles(array $paths): bool
    {
        return Storage::disk($this->getStorageDisk())->delete($paths);
    }

    /**
     * Delete a directory and all its contents
     *
     * @param string $directory
     * @return bool
     */
    public function deleteDirectory(string $directory): bool
    {
        return Storage::disk($this->getStorageDisk())->deleteDirectory($directory);
    }

    /**
     * Check if a file exists
     *
     * @param string $path
     * @return bool
     */
    public function fileExists(string $path): bool
    {
        return Storage::disk($this->getStorageDisk())->exists($path);
    }

    /**
     * Get file size in bytes
     *
     * @param string $path
     * @return int
     */
    public function getFileSize(string $path): int
    {
        return Storage::disk($this->getStorageDisk())->size($path);
    }

    /**
     * Get file's last modified timestamp
     *
     * @param string $path
     * @return int
     */
    public function getFileLastModified(string $path): int
    {
        return Storage::disk($this->getStorageDisk())->lastModified($path);
    }

    /**
     * Get file's MIME type
     *
     * @param string $path
     * @return string|false
     */
    public function getFileMimeType(string $path)
    {
        return Storage::disk($this->getStorageDisk())->mimeType($path);
    }

    /**
     * Copy a file to a new location
     *
     * @param string $from
     * @param string $to
     * @return bool
     */
    public function copyFile(string $from, string $to): bool
    {
        return Storage::disk($this->getStorageDisk())->copy($from, $to);
    }

    /**
     * Move a file to a new location
     *
     * @param string $from
     * @param string $to
     * @return bool
     */
    public function moveFile(string $from, string $to): bool
    {
        return Storage::disk($this->getStorageDisk())->move($from, $to);
    }

    /**
     * Get all files in a directory
     *
     * @param string|null $directory
     * @param bool $recursive
     * @return array
     */
    public function getFiles(?string $directory = null, bool $recursive = false): array
    {
        $disk = Storage::disk($this->getStorageDisk());

        return $recursive
            ? $disk->allFiles($directory)
            : $disk->files($directory);
    }

    /**
     * Get all directories in a directory
     *
     * @param string|null $directory
     * @param bool $recursive
     * @return array
     */
    public function getDirectories(?string $directory = null, bool $recursive = false): array
    {
        $disk = Storage::disk($this->getStorageDisk());

        return $recursive
            ? $disk->allDirectories($directory)
            : $disk->directories($directory);
    }

    /**
     * Download a file
     *
     * @param string $path
     * @param string|null $name
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadFile(string $path, ?string $name = null)
    {
        return Storage::disk($this->getStorageDisk())->download($path, $name);
    }

    /**
     * Get file contents
     *
     * @param string $path
     * @return string|null
     */
    public function getFileContents(string $path): ?string
    {
        return Storage::disk($this->getStorageDisk())->get($path);
    }

    /**
     * Put contents to a file
     *
     * @param string $path
     * @param string $contents
     * @return bool
     */
    public function putFileContents(string $path, string $contents): bool
    {
        return Storage::disk($this->getStorageDisk())->put($path, $contents);
    }

    /**
     * Append contents to a file
     *
     * @param string $path
     * @param string $contents
     * @return bool
     */
    public function appendFileContents(string $path, string $contents): bool
    {
        return Storage::disk($this->getStorageDisk())->append($path, $contents);
    }

    /**
     * Prepend contents to a file
     *
     * @param string $path
     * @param string $contents
     * @return bool
     */
    public function prependFileContents(string $path, string $contents): bool
    {
        return Storage::disk($this->getStorageDisk())->prepend($path, $contents);
    }

    /**
     * Set file visibility
     *
     * @param string $path
     * @param string $visibility ('public' or 'private')
     * @return bool
     */
    public function setFileVisibility(string $path, string $visibility): bool
    {
        return Storage::disk($this->getStorageDisk())->setVisibility($path, $visibility);
    }

    /**
     * Get file visibility
     *
     * @param string $path
     * @return string
     */
    public function getFileVisibility(string $path): string
    {
        return Storage::disk($this->getStorageDisk())->getVisibility($path);
    }

    /**
     * Store file from another disk
     *
     * @param string $sourceDisk
     * @param string $sourcePath
     * @param string $destinationPath
     * @return bool
     */
    public function copyFromDisk(string $sourceDisk, string $sourcePath, string $destinationPath): bool
    {
        $contents = Storage::disk($sourceDisk)->get($sourcePath);
        return Storage::disk($this->getStorageDisk())->put($destinationPath, $contents);
    }

    /**
     * Move file from another disk
     *
     * @param string $sourceDisk
     * @param string $sourcePath
     * @param string $destinationPath
     * @return bool
     */
    public function moveFromDisk(string $sourceDisk, string $sourcePath, string $destinationPath): bool
    {
        if ($this->copyFromDisk($sourceDisk, $sourcePath, $destinationPath)) {
            return Storage::disk($sourceDisk)->delete($sourcePath);
        }
        return false;
    }

    /**
     * Generate a unique file name
     *
     * @param UploadedFile $file
     * @param string|null $prefix
     * @return string
     */
    public function generateFileName(UploadedFile $file, ?string $prefix = null): string
    {
        $extension = $file->getClientOriginalExtension();
        $hash = md5(uniqid() . $file->getClientOriginalName());

        if ($prefix) {
            return "{$prefix}_{$hash}.{$extension}";
        }

        return "{$hash}.{$extension}";
    }

    /**
     * Store file with auto-generated name
     *
     * @param UploadedFile $file
     * @param string|null $directory
     * @param string|null $prefix
     * @return array ['path' => string, 'name' => string]
     */
    public function storeFileWithName(UploadedFile $file, ?string $directory = null, ?string $prefix = null): array
    {
        $name = $this->generateFileName($file, $prefix);
        $path = $this->storeFile($file, $directory, $name);

        return [
            'path' => $path,
            'name' => $name,
        ];
    }
}
