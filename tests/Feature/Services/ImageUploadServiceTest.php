<?php

use App\Services\ImageUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// ============================================================
// ImageUploadService
// Tests cover WebP output format for all resized variants,
// original file preservation, and basic upload pipeline.
// ============================================================

beforeEach(function () {
    Storage::fake('accommodation_draft_photos');
    Storage::fake('accommodation_photos');
    Storage::fake('user_profile_photos');

    $this->service = new ImageUploadService(
        disk: 'accommodation_draft_photos',
        sizes: [
            'thumbnail' => ['width' => 100, 'height' => 100, 'quality' => 80, 'crop' => true],
            'medium' => ['width' => 300, 'height' => 200, 'quality' => 85, 'crop' => false],
        ],
        maxFileSize: 10485760,
        allowedMimeTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'],
    );
});

// ============================================================
// WebP output format
// ============================================================

describe('ImageUploadService – WebP output', function () {

    it('produces .webp paths for all resized variants when uploading a JPEG', function () {
        $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

        $result = $this->service->upload($file, 'draft-test');

        expect($result['resized_paths']['thumbnail'])->toEndWith('.webp')
            ->and($result['resized_paths']['medium'])->toEndWith('.webp');
    });

    it('produces .webp paths for all resized variants when uploading a PNG', function () {
        $file = UploadedFile::fake()->image('photo.png', 800, 600);

        $result = $this->service->upload($file, 'draft-test');

        expect($result['resized_paths']['thumbnail'])->toEndWith('.webp')
            ->and($result['resized_paths']['medium'])->toEndWith('.webp');
    });

    it('produces .webp paths for all resized variants when uploading a WebP', function () {
        $file = UploadedFile::fake()->image('photo.webp', 800, 600);

        $result = $this->service->upload($file, 'draft-test');

        expect($result['resized_paths']['thumbnail'])->toEndWith('.webp')
            ->and($result['resized_paths']['medium'])->toEndWith('.webp');
    });

    it('preserves the original file in its uploaded format', function () {
        $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

        $result = $this->service->upload($file, 'draft-test');

        expect($result['original_path'])->toEndWith('.jpg');
    });

    it('stores resized WebP files on the correct disk', function () {
        $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

        $result = $this->service->upload($file, 'draft-test');

        Storage::disk('accommodation_draft_photos')->assertExists($result['resized_paths']['thumbnail']);
        Storage::disk('accommodation_draft_photos')->assertExists($result['resized_paths']['medium']);
    });

    it('stores the original file on the correct disk', function () {
        $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

        $result = $this->service->upload($file, 'draft-test');

        Storage::disk('accommodation_draft_photos')->assertExists($result['original_path']);
    });

    it('places resized files in named size subdirectories', function () {
        $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

        $result = $this->service->upload($file, 'draft-test');

        expect($result['resized_paths']['thumbnail'])->toContain('/thumbnail/')
            ->and($result['resized_paths']['medium'])->toContain('/medium/');
    });

    it('places original file in the original subdirectory', function () {
        $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

        $result = $this->service->upload($file, 'draft-test');

        expect($result['original_path'])->toContain('/original/');
    });
});

// ============================================================
// Return structure
// ============================================================

describe('ImageUploadService – return structure', function () {

    it('returns all expected top-level keys', function () {
        $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

        $result = $this->service->upload($file, 'draft-test');

        expect($result)->toHaveKeys(['original_path', 'resized_paths', 'dimensions', 'metadata', 'file_info']);
    });

    it('returns correct dimension data', function () {
        $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

        $result = $this->service->upload($file, 'draft-test');

        expect($result['dimensions']['width'])->toBe(800)
            ->and($result['dimensions']['height'])->toBe(600);
    });

    it('returns file info with original filename and size', function () {
        $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

        $result = $this->service->upload($file, 'draft-test');

        expect($result['file_info']['original_filename'])->toBe('photo.jpg')
            ->and($result['file_info']['file_size'])->toBeGreaterThan(0);
    });
});

// ============================================================
// Validation
// ============================================================

describe('ImageUploadService – validation', function () {

    it('throws an exception when the file exceeds the max size', function () {
        $service = new ImageUploadService(
            disk: 'accommodation_draft_photos',
            maxFileSize: 100, // 100 bytes — any real image will exceed this
        );

        $file = UploadedFile::fake()->image('photo.jpg', 100, 100);

        expect(fn () => $service->upload($file, 'draft-test'))->toThrow(Exception::class);
    });

    it('throws an exception for a disallowed MIME type', function () {
        $service = new ImageUploadService(
            disk: 'accommodation_draft_photos',
            allowedMimeTypes: ['image/png'], // only PNG allowed
        );

        $file = UploadedFile::fake()->image('photo.jpg', 100, 100);

        expect(fn () => $service->upload($file, 'draft-test'))->toThrow(Exception::class);
    });
});
