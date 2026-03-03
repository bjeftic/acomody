<?php

// ============================================================
// GET /api/fees/fee-types
// ============================================================

describe('GET /api/fees/fee-types', function () {

    it('returns 401 for unauthenticated requests', function () {
        $this->getJson(route('api.fees.fee-types'))
            ->assertUnauthorized();
    });

    it('returns 200 with correct JSON structure for an authenticated user', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.fees.fee-types'))
            ->assertSuccessful()
            ->assertJson(['success' => true, 'message' => 'Fee types retrieved successfully.'])
            ->assertJsonStructure(['success', 'message', 'data', 'meta']);
    });

    it('returns a non-empty data array', function () {
        $user = authenticatedUser();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.fees.fee-types'))
            ->assertSuccessful();

        expect(count($response->json('data')))->toBeGreaterThan(0);
    });

    it('returns meta.total matching data count', function () {
        $user = authenticatedUser();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.fees.fee-types'))
            ->assertSuccessful();

        expect($response->json('meta.total'))->toBe(count($response->json('data')));
    });

});

// ============================================================
// GET /api/fees/charge-types
// ============================================================

describe('GET /api/fees/charge-types', function () {

    it('returns 401 for unauthenticated requests', function () {
        $this->getJson(route('api.fees.charge-types'))
            ->assertUnauthorized();
    });

    it('returns 200 with correct JSON structure for an authenticated user', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.fees.charge-types'))
            ->assertSuccessful()
            ->assertJson(['success' => true, 'message' => 'Charge types retrieved successfully.'])
            ->assertJsonStructure(['success', 'message', 'data', 'meta']);
    });

    it('returns a non-empty data array', function () {
        $user = authenticatedUser();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.fees.charge-types'))
            ->assertSuccessful();

        expect(count($response->json('data')))->toBeGreaterThan(0);
    });

    it('returns meta.total matching data count', function () {
        $user = authenticatedUser();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.fees.charge-types'))
            ->assertSuccessful();

        expect($response->json('meta.total'))->toBe(count($response->json('data')));
    });

});
