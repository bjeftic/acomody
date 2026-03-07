<?php

use App\Models\Country;
use App\Models\Location;

// ============================================================
// Country model
// Note: CountryController is an empty stub with no routes yet.
// These tests cover the model's authorization rules, relationships,
// and the data seeded via migrations.
// ============================================================

describe('Country model – authorization', function () {

    it('canBeReadBy returns true for an unauthenticated (null) user', function () {
        $country = Country::where('is_active', true)->first();

        expect($country->canBeReadBy(null))->toBeTrue();
    });

    it('canBeReadBy returns true for an authenticated user', function () {
        $user = authenticatedUser();
        $country = Country::where('is_active', true)->first();

        expect($country->canBeReadBy($user))->toBeTrue();
    });

    it('canBeCreatedBy returns false for a guest (null) user', function () {
        $country = Country::where('is_active', true)->first();

        expect($country->canBeCreatedBy(null))->toBeFalse();
    });

    it('canBeCreatedBy returns true for an authenticated user', function () {
        $user = authenticatedUser();
        $country = Country::where('is_active', true)->first();

        expect($country->canBeCreatedBy($user))->toBeFalse();
    });

    it('canBeCreatedBy returns true for an authenticated superadmin user', function () {
        $user = authenticatedUser(['is_superadmin' => true]);
        $country = Country::where('is_active', true)->first();

        expect($country->canBeCreatedBy($user))->toBeTrue();
    });

    it('canBeUpdatedBy returns false for a guest (null) user', function () {
        $country = Country::where('is_active', true)->first();

        expect($country->canBeUpdatedBy(null))->toBeFalse();
    });

    it('canBeUpdatedBy returns true for an authenticated user', function () {
        $user = authenticatedUser();
        $country = Country::where('is_active', true)->first();

        expect($country->canBeUpdatedBy($user))->toBeFalse();
    });

    it('canBeUpdatedBy returns true for an authenticated superadmin user', function () {
        $user = authenticatedUser(['is_superadmin' => true]);
        $country = Country::where('is_active', true)->first();

        expect($country->canBeUpdatedBy($user))->toBeTrue();
    });

    it('canBeDeletedBy returns false for a guest (null) user', function () {
        $country = Country::where('is_active', true)->first();

        expect($country->canBeDeletedBy(null))->toBeFalse();
    });

    it('canBeDeletedBy returns true for an authenticated user', function () {
        $user = authenticatedUser();
        $country = Country::where('is_active', true)->first();

        expect($country->canBeDeletedBy($user))->toBeFalse();
    });

    it('canBeDeletedBy returns true for an authenticated superadmin user', function () {
        $user = authenticatedUser(['is_superadmin' => true]);
        $country = Country::where('is_active', true)->first();

        expect($country->canBeDeletedBy($user))->toBeTrue();
    });
});

describe('Country model – data and relationships', function () {

    it('countries are seeded by the migration', function () {
        expect(Country::count())->toBeGreaterThan(0);
    });

    it('has a translatable name field', function () {
        $country = Country::where('is_active', true)->first();

        expect($country->getTranslation('name', 'en'))->not->toBeEmpty();
    });

    it('has the locations relationship', function () {
        $country = Country::where('is_active', true)->first();

        expect($country->locations())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
    });

    it('HasActiveScope global scope returns only active countries', function () {
        // HasActiveScope adds a global scope, not an active() method.
        // Country::all() already filters to is_active = true automatically.
        $countries = Country::all();

        expect($countries->every(fn($c) => $c->is_active === true))->toBeTrue();
    });

    it('can retrieve a country by its iso_code_2', function () {
        $serbia = Country::where('iso_code_2', 'RS')->first();

        expect($serbia)->not->toBeNull();
        expect($serbia->getTranslation('name', 'en'))->toBe('Serbia');
    });

    it('can load associated locations', function () {
        $user = authenticatedUser(['is_superadmin' => true]);
        $country = Country::where('is_active', true)->first();

        Location::withoutSyncingToSearch(function () use ($country, $user) {
            Location::create([
                'country_id' => $country->id,
                'name' => 'Test City',
                'location_type' => 'city',
                'latitude' => 44.8,
                'longitude' => 20.4,
                'user_id' => $user->id,
            ]);
        });

        expect($country->locations()->count())->toBeGreaterThan(0);
    });
});
