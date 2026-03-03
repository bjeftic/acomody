<?php

use App\Models\Amenity;

// ============================================================
// GET /api/amenities
// ============================================================

describe('GET /api/amenities', function () {

    it('returns 401 for unauthenticated requests', function () {
        $this->getJson(route('api.amenities'))
            ->assertUnauthorized();
    });

    it('returns 200 with correct JSON structure for an authenticated user', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.amenities'))
            ->assertSuccessful()
            ->assertJson(['success' => true, 'message' => 'Amenities retrieved successfully.'])
            ->assertJsonStructure(['success', 'message', 'data', 'meta']);
    });

    it('returns amenities seeded from migrations', function () {
        $user = authenticatedUser();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.amenities'))
            ->assertSuccessful();

        expect(count($response->json('data')))->toBeGreaterThan(0);
    });

    it('returns meta.total matching the number of amenities in the database', function () {
        $user = authenticatedUser();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.amenities'))
            ->assertSuccessful();

        $total = $response->json('meta.total');

        expect($total)->toBe(Amenity::count());
    });

    it('response success flag is true', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.amenities'))
            ->assertSuccessful()
            ->assertJson(['success' => true]);
    });

});
