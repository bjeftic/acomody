<?php

use App\Enums\Location\LocationType;
use App\Models\Country;
use App\Models\Location;
use App\Models\Photo;
use App\Services\PhotoService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// ============================================================
// Helpers
// ============================================================

function superadmin(): \App\Models\User
{
    return authenticatedUser(['is_superadmin' => true]);
}

function makeLocation(array $attributes = []): Location
{
    $superadmin = superadmin();
    $country = Country::where('is_active', true)->first();

    return Location::withoutSyncingToSearch(fn () => Location::create(array_merge([
        'name' => 'Test City',
        'country_id' => $country->id,
        'location_type' => LocationType::CITY->value,
        'latitude' => 44.8,
        'longitude' => 20.4,
        'is_active' => true,
        'user_id' => $superadmin->id,
    ], $attributes)));
}

function validLocationPayload(array $overrides = []): array
{
    $country = Country::where('is_active', true)->first();

    return array_merge([
        'name' => ['en' => 'Belgrade', 'sr' => 'Beograd', 'de' => 'Belgrad'],
        'country' => $country->id,
        'location_type' => LocationType::CITY->value,
        'latitude' => '44.8167',
        'longitude' => '20.4667',
        'is_active' => '1',
    ], $overrides);
}

// ============================================================
// Index
// ============================================================

describe('GET /admin/locations', function () {
    it('is accessible to superadmins', function () {
        superadmin();
        makeLocation();

        $this->get('/admin/locations')->assertSuccessful();
    });

    it('redirects guests to login', function () {
        $this->get('/admin/locations')->assertRedirect();
    });

    it('redirects regular users away', function () {
        authenticatedUser();

        $this->get('/admin/locations')->assertRedirect();
    });

    it('lists location names', function () {
        superadmin();
        makeLocation(['name' => 'Novi Sad']);

        $this->get('/admin/locations')->assertSee('Novi Sad');
    });
});

// ============================================================
// Create / Store
// ============================================================

describe('POST /admin/locations', function () {
    it('creates a location without image', function () {
        superadmin();

        $this->post('/admin/locations', validLocationPayload())
            ->assertRedirect('/admin/locations');

        $this->assertDatabaseHas('locations', ['location_type' => LocationType::CITY->value]);
    });

    it('creates a location with an image', function () {
        Storage::fake('location_photos');
        superadmin();

        $file = UploadedFile::fake()->image('cover.jpg', 800, 600);

        $this->post('/admin/locations', array_merge(
            validLocationPayload(),
            ['image' => $file]
        ))->assertRedirect('/admin/locations');

        $location = Location::withoutSyncingToSearch(fn () => Location::latest()->first());

        expect($location->photos()->count())->toBe(1);
        expect($location->primaryPhoto->is_primary)->toBeTrue();
    });

    it('sets is_active to false when checkbox is unchecked', function () {
        superadmin();

        // HTML forms omit unchecked checkboxes entirely — no is_active key in payload
        $payload = validLocationPayload(['is_active' => '0']);

        $this->post('/admin/locations', $payload)
            ->assertRedirect('/admin/locations');

        $location = Location::withoutSyncingToSearch(fn () => Location::latest()->first());
        expect($location->is_active)->toBeFalse();
    });

    it('accepts a location without coordinates', function () {
        superadmin();

        $payload = validLocationPayload(['latitude' => '', 'longitude' => '']);

        $this->post('/admin/locations', $payload)
            ->assertRedirect('/admin/locations');

        $location = Location::withoutSyncingToSearch(fn () => Location::latest()->first());
        expect($location->latitude)->toBeNull();
        expect($location->longitude)->toBeNull();
    });

    it('rejects invalid image type', function () {
        superadmin();

        $file = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');

        $this->post('/admin/locations', array_merge(
            validLocationPayload(),
            ['image' => $file]
        ))->assertSessionHasErrors('image');
    });

    it('rejects image over 5MB', function () {
        superadmin();

        $file = UploadedFile::fake()->image('big.jpg')->size(6000);

        $this->post('/admin/locations', array_merge(
            validLocationPayload(),
            ['image' => $file]
        ))->assertSessionHasErrors('image');
    });

    it('requires english name', function () {
        superadmin();

        $this->post('/admin/locations', validLocationPayload(['name' => ['en' => '', 'sr' => '', 'de' => '']]))
            ->assertSessionHasErrors('name.en');
    });

    it('requires a valid country', function () {
        superadmin();

        $this->post('/admin/locations', validLocationPayload(['country' => 99999]))
            ->assertSessionHasErrors('country');
    });

    it('requires a valid location_type', function () {
        superadmin();

        $this->post('/admin/locations', validLocationPayload(['location_type' => 'invalid']))
            ->assertSessionHasErrors('location_type');
    });
});

// ============================================================
// Edit / Update
// ============================================================

describe('GET /admin/locations/{id}/edit', function () {
    it('shows the edit form', function () {
        superadmin();
        $location = makeLocation(['name' => 'Subotica']);

        $this->get("/admin/locations/{$location->id}/edit")
            ->assertSuccessful()
            ->assertSee('Subotica');
    });

    it('redirects regular users away', function () {
        $location = makeLocation(); // creates as superadmin
        authenticatedUser();        // switch to regular user

        $this->get("/admin/locations/{$location->id}/edit")->assertRedirect();
    });
});

describe('PUT /admin/locations/{id}', function () {
    it('updates location fields', function () {
        superadmin();
        $location = makeLocation();

        $this->put("/admin/locations/{$location->id}", validLocationPayload(['name' => ['en' => 'Updated City']]))
            ->assertRedirect('/admin/locations');

        expect($location->fresh()->getTranslation('name', 'en'))->toBe('Updated City');
    });

    it('replaces the existing photo on update', function () {
        superadmin();
        $location = makeLocation();

        $mock = $this->mock(PhotoService::class);
        $mock->shouldReceive('deletePhoto')->once();
        $mock->shouldReceive('uploadPhoto')->once()->andReturn(new Photo);

        Photo::create([
            'photoable_type' => Location::class,
            'photoable_id' => $location->id,
            'disk' => 'location_photos',
            'path' => 'location-1/original/old.jpg',
            'original_filename' => 'old.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 100000,
            'order' => 0,
            'is_primary' => true,
            'status' => 'completed',
            'uploaded_at' => now(),
        ]);

        $file = UploadedFile::fake()->image('new.jpg', 800, 600);

        $this->put("/admin/locations/{$location->id}", array_merge(
            validLocationPayload(),
            ['image' => $file]
        ))->assertRedirect('/admin/locations');
    });

    it('removes existing image when remove_image is checked', function () {
        superadmin();
        $location = makeLocation();

        $mock = $this->mock(PhotoService::class);
        $mock->shouldReceive('deletePhoto')->once();
        $mock->shouldReceive('uploadPhoto')->never();

        Photo::create([
            'photoable_type' => Location::class,
            'photoable_id' => $location->id,
            'disk' => 'location_photos',
            'path' => 'location-1/original/photo.jpg',
            'original_filename' => 'photo.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 100000,
            'order' => 0,
            'is_primary' => true,
            'status' => 'completed',
            'uploaded_at' => now(),
        ]);

        $this->put("/admin/locations/{$location->id}", array_merge(validLocationPayload(), ['remove_image' => '1']))
            ->assertRedirect('/admin/locations');
    });

    it('updates without touching photo when no image submitted', function () {
        superadmin();
        $location = makeLocation();

        $this->put("/admin/locations/{$location->id}", validLocationPayload(['name' => ['en' => 'No Photo Update']]))
            ->assertRedirect('/admin/locations');

        expect($location->fresh()->getTranslation('name', 'en'))->toBe('No Photo Update');
        expect($location->photos()->count())->toBe(0);
    });
});

// ============================================================
// Destroy
// ============================================================

describe('DELETE /admin/locations/{id}', function () {
    it('deletes a location', function () {
        superadmin();
        $location = makeLocation();

        $this->delete("/admin/locations/{$location->id}")
            ->assertRedirect('/admin/locations');

        $this->assertDatabaseMissing('locations', ['id' => $location->id]);
    });

    it('deletes associated photos on destroy', function () {
        superadmin();
        $location = makeLocation();

        $mock = $this->mock(PhotoService::class);
        $mock->shouldReceive('deletePhoto')->once();

        Photo::create([
            'photoable_type' => Location::class,
            'photoable_id' => $location->id,
            'disk' => 'location_photos',
            'path' => 'location-1/original/photo.jpg',
            'original_filename' => 'photo.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 100000,
            'order' => 0,
            'is_primary' => true,
            'status' => 'completed',
            'uploaded_at' => now(),
        ]);

        $this->delete("/admin/locations/{$location->id}")
            ->assertRedirect('/admin/locations');
    });

    it('redirects regular users away', function () {
        $location = makeLocation(); // creates as superadmin
        authenticatedUser();        // switch to regular user

        $this->delete("/admin/locations/{$location->id}")->assertRedirect();
    });
});
