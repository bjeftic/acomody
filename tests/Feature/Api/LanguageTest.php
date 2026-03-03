<?php

use App\Models\Language;

// ============================================================
// Language model
// Note: LanguageController is an empty stub with no routes yet.
// These tests cover the model's authorization rules and the data
// seeded via migrations.
// ============================================================

describe('Language model – authorization', function () {

    it('canBeReadBy returns true for a guest (null) user', function () {
        $language = Language::first();

        expect($language->canBeReadBy(null))->toBeTrue();
    });

    it('canBeReadBy returns true for an authenticated user', function () {
        $user = authenticatedUser();
        $language = Language::first();

        expect($language->canBeReadBy($user))->toBeTrue();
    });

    it('canBeCreatedBy returns false for a guest (null) user', function () {
        $language = Language::first();

        expect($language->canBeCreatedBy(null))->toBeFalse();
    });

    it('canBeCreatedBy returns true for an authenticated user', function () {
        $user = authenticatedUser();
        $language = Language::first();

        expect($language->canBeCreatedBy($user))->toBeTrue();
    });

    it('canBeUpdatedBy returns false for a guest (null) user', function () {
        $language = Language::first();

        expect($language->canBeUpdatedBy(null))->toBeFalse();
    });

    it('canBeUpdatedBy returns true for an authenticated user', function () {
        $user = authenticatedUser();
        $language = Language::first();

        expect($language->canBeUpdatedBy($user))->toBeTrue();
    });

    it('canBeDeletedBy returns false for a guest (null) user', function () {
        $language = Language::first();

        expect($language->canBeDeletedBy(null))->toBeFalse();
    });

    it('canBeDeletedBy returns true for an authenticated user', function () {
        $user = authenticatedUser();
        $language = Language::first();

        expect($language->canBeDeletedBy($user))->toBeTrue();
    });

});

describe('Language model – data', function () {

    it('languages are seeded by the migration', function () {
        expect(Language::count())->toBeGreaterThan(0);
    });

    it('has a translatable name field', function () {
        $language = Language::first();

        expect($language->getTranslation('name', 'en'))->not->toBeEmpty();
    });

    it('has an iso_code field', function () {
        $language = Language::first();

        expect($language->iso_code)->not->toBeEmpty();
    });

    it('HasActiveScope global scope returns only active languages', function () {
        // HasActiveScope adds a global scope, not an active() method.
        // Language::all() already filters to is_active = true automatically.
        $languages = Language::all();

        expect($languages->every(fn ($l) => $l->is_active === true))->toBeTrue();
    });

    it('can retrieve English by its iso_code', function () {
        $english = Language::where('iso_code', 'en')->first();

        expect($english)->not->toBeNull();
    });

    it('can retrieve Serbian by its iso_code', function () {
        $serbian = Language::where('iso_code', 'sr')->first();

        expect($serbian)->not->toBeNull();
    });

    it('has is_active boolean field', function () {
        $language = Language::first();

        expect($language->is_active)->toBeIn([true, false]);
    });

});
