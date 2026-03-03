<?php

use App\Models\AccommodationDraft;
use App\Models\Photo;
use Illuminate\Support\Facades\Storage;

// ============================================================
// Photo model
// Note: PhotoController is an empty stub with no routes yet.
// These tests cover the model's scopes, computed attributes,
// polymorphic relationship, and file-deletion behaviour.
//
// The photos table requires photoable_type and photoable_id to
// be non-null. A beforeEach creates an AccommodationDraft to
// use as the polymorphic parent in every create() call.
// ============================================================

beforeEach(function () {
    $this->draft = AccommodationDraft::withoutAuthorization(
        fn () => AccommodationDraft::factory()->create()
    );
});

// ============================================================
// Creation and basic structure
// ============================================================

describe('Photo model – creation', function () {

    it('can be created via factory', function () {
        $photo = Photo::factory()->create([
            'photoable_type' => AccommodationDraft::class,
            'photoable_id' => $this->draft->id,
        ]);

        expect($photo->id)->not->toBeNull();
        $this->assertDatabaseHas('photos', ['id' => $photo->id]);
    });

    it('uses a ULID as primary key', function () {
        $photo = Photo::factory()->create([
            'photoable_type' => AccommodationDraft::class,
            'photoable_id' => $this->draft->id,
        ]);

        // ULIDs are 26-character base32 strings
        expect(strlen($photo->id))->toBe(26);
    });

    it('defaults status to completed', function () {
        $photo = Photo::factory()->create([
            'photoable_type' => AccommodationDraft::class,
            'photoable_id' => $this->draft->id,
        ]);

        expect($photo->status)->toBe('completed');
    });

    it('defaults is_primary to false', function () {
        $photo = Photo::factory()->create([
            'photoable_type' => AccommodationDraft::class,
            'photoable_id' => $this->draft->id,
        ]);

        expect($photo->is_primary)->toBeFalse();
    });

    it('casts is_primary to boolean', function () {
        $photo = Photo::factory()->create([
            'photoable_type' => AccommodationDraft::class,
            'photoable_id' => $this->draft->id,
            'is_primary' => true,
        ]);

        expect($photo->is_primary)->toBeTrue()
            ->and($photo->is_primary)->toBeBool();
    });

    it('casts metadata to array', function () {
        $photo = Photo::factory()->create([
            'photoable_type' => AccommodationDraft::class,
            'photoable_id' => $this->draft->id,
        ]);

        expect($photo->metadata)->toBeArray();
    });

    it('casts uploaded_at to a Carbon datetime', function () {
        $photo = Photo::factory()->create([
            'photoable_type' => AccommodationDraft::class,
            'photoable_id' => $this->draft->id,
            'uploaded_at' => now(),
        ]);

        expect($photo->uploaded_at)->toBeInstanceOf(\Carbon\Carbon::class);
    });

});

// ============================================================
// Scopes
// ============================================================

describe('Photo model – scopes', function () {

    it('completed() scope returns only photos with status = completed', function () {
        Photo::factory()->create(['photoable_type' => AccommodationDraft::class, 'photoable_id' => $this->draft->id, 'status' => 'completed']);
        Photo::factory()->create(['photoable_type' => AccommodationDraft::class, 'photoable_id' => $this->draft->id, 'status' => 'pending']);
        Photo::factory()->create(['photoable_type' => AccommodationDraft::class, 'photoable_id' => $this->draft->id, 'status' => 'failed']);

        $completed = Photo::completed()->get();

        expect($completed->every(fn ($p) => $p->status === 'completed'))->toBeTrue();
    });

    it('completed() scope excludes non-completed photos', function () {
        $pending = Photo::factory()->pending()->create([
            'photoable_type' => AccommodationDraft::class,
            'photoable_id' => $this->draft->id,
        ]);

        $completedIds = Photo::completed()->pluck('id');

        expect($completedIds)->not->toContain($pending->id);
    });

    it('ordered() scope orders by the order column ascending', function () {
        Photo::factory()->order(3)->create(['photoable_type' => AccommodationDraft::class, 'photoable_id' => $this->draft->id]);
        Photo::factory()->order(1)->create(['photoable_type' => AccommodationDraft::class, 'photoable_id' => $this->draft->id]);
        Photo::factory()->order(2)->create(['photoable_type' => AccommodationDraft::class, 'photoable_id' => $this->draft->id]);

        $orders = Photo::ordered()->pluck('order')->toArray();

        expect($orders)->toBe([1, 2, 3]);
    });

    it('primary() scope returns only photos where is_primary = true', function () {
        Photo::factory()->primary()->create(['photoable_type' => AccommodationDraft::class, 'photoable_id' => $this->draft->id]);
        Photo::factory()->create(['photoable_type' => AccommodationDraft::class, 'photoable_id' => $this->draft->id]); // not primary

        $primaryPhotos = Photo::primary()->get();

        expect($primaryPhotos->every(fn ($p) => $p->is_primary === true))->toBeTrue();
    });

    it('primary() scope excludes non-primary photos', function () {
        $nonPrimary = Photo::factory()->create([
            'photoable_type' => AccommodationDraft::class,
            'photoable_id' => $this->draft->id,
            'is_primary' => false,
        ]);

        $primaryIds = Photo::primary()->pluck('id');

        expect($primaryIds)->not->toContain($nonPrimary->id);
    });

});

// ============================================================
// Factory states
// ============================================================

describe('Photo factory states', function () {

    it('pending() state sets status to pending', function () {
        $photo = Photo::factory()->pending()->create([
            'photoable_type' => AccommodationDraft::class,
            'photoable_id' => $this->draft->id,
        ]);

        expect($photo->status)->toBe('pending');
    });

    it('processing() state sets status to processing', function () {
        $photo = Photo::factory()->processing()->create([
            'photoable_type' => AccommodationDraft::class,
            'photoable_id' => $this->draft->id,
        ]);

        expect($photo->status)->toBe('processing');
    });

    it('failed() state sets status to failed', function () {
        $photo = Photo::factory()->failed()->create([
            'photoable_type' => AccommodationDraft::class,
            'photoable_id' => $this->draft->id,
        ]);

        expect($photo->status)->toBe('failed');
        expect($photo->error_message)->not->toBeNull();
    });

    it('primary() state sets is_primary = true and order = 0', function () {
        $photo = Photo::factory()->primary()->create([
            'photoable_type' => AccommodationDraft::class,
            'photoable_id' => $this->draft->id,
        ]);

        expect($photo->is_primary)->toBeTrue();
        expect($photo->order)->toBe(0);
    });

    it('forAccommodation() state uses the accommodation_photos disk', function () {
        $photo = Photo::factory()->forAccommodation()->create([
            'photoable_type' => AccommodationDraft::class,
            'photoable_id' => $this->draft->id,
        ]);

        expect($photo->disk)->toBe('accommodation_photos');
    });

    it('forDraft() state uses the accommodation_draft_photos disk', function () {
        $photo = Photo::factory()->forDraft()->create([
            'photoable_type' => AccommodationDraft::class,
            'photoable_id' => $this->draft->id,
        ]);

        expect($photo->disk)->toBe('accommodation_draft_photos');
    });

    it('dimensions() state sets width and height', function () {
        $photo = Photo::factory()->dimensions(1920, 1080)->create([
            'photoable_type' => AccommodationDraft::class,
            'photoable_id' => $this->draft->id,
        ]);

        expect($photo->width)->toBe(1920)
            ->and($photo->height)->toBe(1080);
    });

});

// ============================================================
// URL attributes
// ============================================================

describe('Photo model – URL attributes', function () {

    it('url attribute returns null when path is empty', function () {
        $photo = Photo::factory()->make(['path' => '']);

        expect($photo->url)->toBeNull();
    });

    it('urls attribute always returns an array with four size keys', function () {
        $photo = Photo::factory()->make();

        expect($photo->urls)->toBeArray()
            ->toHaveKeys(['original', 'large', 'medium', 'thumbnail']);
    });

    it('thumbnail_url falls back to the original url when thumbnail_path is empty', function () {
        $photo = Photo::factory()->withPicsumPaths()->make(['thumbnail_path' => '']);

        expect($photo->thumbnail_url)->toBe($photo->url);
    });

    it('medium_url falls back to the original url when medium_path is empty', function () {
        $photo = Photo::factory()->withPicsumPaths()->make(['medium_path' => '']);

        expect($photo->medium_url)->toBe($photo->url);
    });

    it('large_url falls back to the original url when large_path is empty', function () {
        $photo = Photo::factory()->withPicsumPaths()->make(['large_path' => '']);

        expect($photo->large_url)->toBe($photo->url);
    });

    it('url returns a picsum URL for seed paths', function () {
        $photo = Photo::factory()->withPicsumPaths()->make();

        expect($photo->url)->toContain('picsum.photos');
    });

});

// ============================================================
// Formatted size
// ============================================================

describe('Photo model – formatted_size attribute', function () {

    it('formats bytes correctly', function () {
        $photo = Photo::factory()->make(['file_size' => 512]);

        expect($photo->formatted_size)->toContain('bytes');
    });

    it('formats kilobytes correctly', function () {
        $photo = Photo::factory()->make(['file_size' => 2048]); // 2 KB

        expect($photo->formatted_size)->toContain('KB');
    });

    it('formats megabytes correctly', function () {
        $photo = Photo::factory()->make(['file_size' => 2 * 1024 * 1024]); // 2 MB

        expect($photo->formatted_size)->toContain('MB');
    });

    it('handles null file_size gracefully', function () {
        $photo = Photo::factory()->make(['file_size' => null]);

        expect($photo->formatted_size)->toBe('0 bytes');
    });

});

// ============================================================
// Polymorphic relationship
// ============================================================

describe('Photo model – polymorphic relationship', function () {

    it('photoable() is a MorphTo relationship', function () {
        $photo = Photo::factory()->create([
            'photoable_type' => AccommodationDraft::class,
            'photoable_id' => $this->draft->id,
        ]);

        expect($photo->photoable())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphTo::class);
    });

    it('photoable() resolves to the correct parent model', function () {
        $photo = Photo::factory()->create([
            'photoable_type' => AccommodationDraft::class,
            'photoable_id' => $this->draft->id,
        ]);

        expect($photo->photoable_type)->toBe(AccommodationDraft::class);
        expect($photo->photoable_id)->toBe($this->draft->id);
    });

});

// ============================================================
// File deletion on model delete
// ============================================================

describe('Photo model – file deletion on delete', function () {

    it('deleteAllFiles returns true', function () {
        Storage::fake('accommodation_draft_photos');

        $photo = Photo::factory()->forDraft()->create([
            'photoable_type' => AccommodationDraft::class,
            'photoable_id' => $this->draft->id,
        ]);

        expect($photo->deleteAllFiles())->toBeTrue();
    });

    it('deleting a photo calls deleteAllFiles', function () {
        Storage::fake('accommodation_draft_photos');

        $photo = Photo::factory()->forDraft()->create([
            'photoable_type' => AccommodationDraft::class,
            'photoable_id' => $this->draft->id,
        ]);

        // Create fake files on the fake disk
        Storage::disk('accommodation_draft_photos')->put($photo->path, 'fake-content');

        $photo->delete();

        Storage::disk('accommodation_draft_photos')->assertMissing($photo->path);
    });

});
