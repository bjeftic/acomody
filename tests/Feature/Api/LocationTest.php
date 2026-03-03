<?php

use App\Enums\Location\LocationType;
use App\Models\Country;
use App\Models\Location;

// ============================================================
// Location model
// Note: LocationController is an empty stub with no routes yet.
// These tests cover the model's behavior, relationships, casting,
// and searchable array structure.
//
// All Location::create() calls use withoutSyncingToSearch() to
// prevent Typesense connection attempts during tests.
// ============================================================

/**
 * Helper to create a Location without triggering Scout sync.
 */
function createLocation(array $attributes = []): Location
{
    $user = authenticatedUser();
    $country = Country::where('is_active', true)->first();

    return Location::withoutSyncingToSearch(function () use ($country, $user, $attributes) {
        return Location::withoutAuthorization(function () use ($country, $user, $attributes) {
            return Location::create(array_merge([
                'country_id' => $country->id,
                'name' => 'Test City',
                'location_type' => LocationType::CITY->value,
                'latitude' => 44.8167,
                'longitude' => 20.4667,
                'user_id' => $user->id,
            ], $attributes));
        });
    });
}

// ============================================================
// Authorization
// ============================================================

describe('Location model – authorization', function () {

    it('canBeReadBy returns true for a guest (null) user', function () {
        $location = createLocation();

        expect($location->canBeReadBy(null))->toBeTrue();
    });

    it('canBeReadBy returns true for an authenticated user', function () {
        $user = authenticatedUser();
        $location = createLocation();

        expect($location->canBeReadBy($user))->toBeTrue();
    });

    it('canBeCreatedBy returns false for a guest (null) user', function () {
        $location = createLocation();

        expect($location->canBeCreatedBy(null))->toBeFalse();
    });

    it('canBeCreatedBy returns true for an authenticated user', function () {
        $user = authenticatedUser();
        $location = createLocation();

        expect($location->canBeCreatedBy($user))->toBeTrue();
    });

    it('canBeUpdatedBy returns false for a guest (null) user', function () {
        $location = createLocation();

        expect($location->canBeUpdatedBy(null))->toBeFalse();
    });

    it('canBeUpdatedBy returns true for an authenticated user', function () {
        $user = authenticatedUser();
        $location = createLocation();

        expect($location->canBeUpdatedBy($user))->toBeTrue();
    });

    it('canBeDeletedBy returns false for a guest (null) user', function () {
        $location = createLocation();

        expect($location->canBeDeletedBy(null))->toBeFalse();
    });

    it('canBeDeletedBy returns true for an authenticated user', function () {
        $user = authenticatedUser();
        $location = createLocation();

        expect($location->canBeDeletedBy($user))->toBeTrue();
    });

});

// ============================================================
// Relationships
// ============================================================

describe('Location model – relationships', function () {

    it('belongs to a country', function () {
        $location = createLocation();

        expect($location->country)->toBeInstanceOf(Country::class);
    });

    it('parent relationship returns null when parent_id is null', function () {
        $location = createLocation(['parent_id' => null]);

        expect($location->parent)->toBeNull();
    });

    it('parent relationship returns the parent location', function () {
        $parent = createLocation();
        $child = createLocation(['parent_id' => $parent->id]);

        Location::withoutSyncingToSearch(function () use ($parent, $child) {
            expect($child->parent->id)->toBe($parent->id);
        });
    });

    it('childrens relationship returns child locations', function () {
        $parent = createLocation();
        createLocation(['parent_id' => $parent->id]);
        createLocation(['parent_id' => $parent->id]);

        expect($parent->childrens()->count())->toBe(2);
    });

    it('country relationship is eager-loadable', function () {
        createLocation();

        $location = Location::withoutSyncingToSearch(fn () => Location::withoutAuthorization(fn () => Location::with('country')->latest()->first()
        )
        );

        expect($location->relationLoaded('country'))->toBeTrue();
    });

});

// ============================================================
// Casting
// ============================================================

describe('Location model – attribute casting', function () {

    it('casts location_type to a LocationType enum', function () {
        $location = createLocation(['location_type' => LocationType::CITY->value]);

        expect($location->location_type)->toBeInstanceOf(LocationType::class);
        expect($location->location_type)->toBe(LocationType::CITY);
    });

    it('casts latitude to a float', function () {
        $location = createLocation(['latitude' => 44.8167]);

        expect($location->latitude)->toBeFloat();
    });

    it('casts longitude to a float', function () {
        $location = createLocation(['longitude' => 20.4667]);

        expect($location->longitude)->toBeFloat();
    });

    it('stores and retrieves the translatable name', function () {
        $location = createLocation(['name' => 'Belgrade']);

        expect($location->fresh()->name)->toBe('Belgrade');
    });

});

// ============================================================
// Searchable
// ============================================================

describe('Location model – searchable array', function () {

    it('searchableAs returns the locations index name', function () {
        $location = createLocation();

        expect($location->searchableAs())->toBe('locations');
    });

    it('toSearchableArray includes required fields', function () {
        $location = createLocation();
        $array = $location->toSearchableArray();

        expect($array)->toHaveKeys(['id', 'type', 'country_id', 'location_type', 'created_at', 'name']);
    });

    it('toSearchableArray type field is "location"', function () {
        $location = createLocation();
        $array = $location->toSearchableArray();

        expect($array['type'])->toBe('location');
    });

    it('toSearchableArray includes geopoint when coordinates are set', function () {
        $location = createLocation(['latitude' => 44.8167, 'longitude' => 20.4667]);
        $array = $location->toSearchableArray();

        expect($array)->toHaveKey('location');
        expect($array['location'])->toBe([44.8167, 20.4667]);
    });

    it('toSearchableArray id is cast to string', function () {
        $location = createLocation();
        $array = $location->toSearchableArray();

        expect($array['id'])->toBeString();
    });

});

// ============================================================
// LocationType enum
// ============================================================

describe('LocationType enum', function () {

    it('has all expected cases', function () {
        $cases = LocationType::cases();
        $values = array_map(fn ($c) => $c->value, $cases);

        expect($values)->toContain('city')
            ->toContain('region')
            ->toContain('state')
            ->toContain('town')
            ->toContain('neighborhood')
            ->toContain('mountain')
            ->toContain('island');
    });

    it('toArray returns array with id, label, and description keys', function () {
        $array = LocationType::toArray();

        foreach ($array as $item) {
            expect($item)->toHaveKeys(['id', 'label', 'description']);
        }
    });

});
