<?php

// ============================================================
// GET /api/accommodation-types
// ============================================================

describe('GET /api/accommodation-types', function () {

    it('returns 401 for unauthenticated requests', function () {
        $this->getJson(route('api.accommodation.types'))
            ->assertUnauthorized();
    });

    it('returns 200 with correct JSON structure for an authenticated user', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.accommodation.types'))
            ->assertSuccessful()
            ->assertJson(['success' => true, 'message' => 'Accommodation types retrieved successfully.'])
            ->assertJsonStructure(['success', 'message', 'data', 'meta']);
    });

    it('returns a non-empty data array', function () {
        $user = authenticatedUser();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.accommodation.types'))
            ->assertSuccessful();

        expect($response->json('data'))->not->toBeEmpty();
    });

    it('returns meta.total matching the number of types', function () {
        $user = authenticatedUser();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.accommodation.types'))
            ->assertSuccessful();

        $total = $response->json('meta.total');
        $count = count($response->json('data'));

        expect($total)->toBe($count);
    });

    it('each type has id, name, and category keys', function () {
        $user = authenticatedUser();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.accommodation.types'))
            ->assertSuccessful();

        foreach ($response->json('data') as $type) {
            expect($type)->toHaveKeys(['id', 'name', 'category']);
        }
    });

});
