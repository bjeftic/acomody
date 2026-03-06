<?php

use App\Models\Accommodation;
use Illuminate\Support\Facades\DB;

// ============================================================
// Public routes — no auth required
// ============================================================

// ============================================================
// GET /api/public/filters
// ============================================================

describe('GET /api/public/filters', function () {

    it('returns 200 without authentication', function () {
        $this->getJson(route('api.publicfilters'))
            ->assertSuccessful();
    });

    it('returns a JSON array', function () {
        $response = $this->getJson(route('api.publicfilters'))
            ->assertSuccessful();

        expect($response->json())->toBeArray();
    });

    it('returns a non-empty array of filter groups', function () {
        $response = $this->getJson(route('api.publicfilters'))
            ->assertSuccessful();

        expect(count($response->json()))->toBeGreaterThan(0);
    });

});

// ============================================================
// GET /api/public/accommodations/{accommodation}
// ============================================================

describe('GET /api/public/accommodations/{accommodation}', function () {

    beforeEach(function () {
        seedCurrencyRates();
    });

    it('returns 200 for an existing active accommodation without authentication', function () {
        $owner = authenticatedUser();
        $accommodation = createAccommodation($owner);

        $this->getJson(route('api.publicaccommodationsshow', $accommodation))
            ->assertSuccessful()
            ->assertJson(['success' => true, 'message' => 'Accommodation retrieved successfully']);
    });

    it('returns the correct accommodation id', function () {
        $owner = authenticatedUser();
        $accommodation = createAccommodation($owner);

        $response = $this->getJson(route('api.publicaccommodationsshow', $accommodation))
            ->assertSuccessful();

        expect($response->json('data.id'))->toBe($accommodation->id);
    });

    it('returns 404 for a non-existent accommodation id', function () {
        $this->getJson(route('api.publicaccommodationsshow', 'non-existent-id'))
            ->assertNotFound();
    });

    it('returns correct JSON structure on success', function () {
        $owner = authenticatedUser();
        $accommodation = createAccommodation($owner);

        $this->getJson(route('api.publicaccommodationsshow', $accommodation))
            ->assertSuccessful()
            ->assertJsonStructure(['success', 'message', 'data']);
    });

    it('returns 200 for an authenticated guest who did not create the accommodation', function () {
        $owner = authenticatedUser();
        $accommodation = createAccommodation($owner);

        $guest = authenticatedUser();
        $this->actingAs($guest)
            ->getJson(route('api.publicaccommodationsshow', $accommodation))
            ->assertSuccessful()
            ->assertJson(['success' => true]);
    });

});
