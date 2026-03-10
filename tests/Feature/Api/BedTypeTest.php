<?php

// ============================================================
// GET /api/bed-types
// ============================================================

describe('GET /api/bed-types', function () {

    it('returns 401 for unauthenticated requests', function () {
        $this->getJson(route('api.bed.types'))
            ->assertUnauthorized();
    });

    it('returns 200 with correct JSON structure for an authenticated user', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.bed.types'))
            ->assertSuccessful()
            ->assertJson(['success' => true, 'message' => 'Bed types retrieved successfully.'])
            ->assertJsonStructure(['success', 'message', 'data', 'meta']);
    });

    it('returns a non-empty data array', function () {
        $user = authenticatedUser();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.bed.types'))
            ->assertSuccessful();

        expect($response->json('data'))->not->toBeEmpty();
    });

    it('each bed type has value, name, and description keys', function () {
        $user = authenticatedUser();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.bed.types'))
            ->assertSuccessful();

        foreach ($response->json('data') as $bedType) {
            expect($bedType)->toHaveKeys(['value', 'name', 'description']);
        }
    });

});
