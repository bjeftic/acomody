<?php

use App\Models\Accommodation;
use App\Services\AccommodationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ============================================================
// INDEX  GET /api/accommodations
// ============================================================

describe('GET /api/accommodations (index)', function () {

    it('returns 401 for unauthenticated requests', function () {
        $this->getJson(route('api.accommodations.accommodations.index'))
            ->assertStatus(401);
    });

    it('returns 200 with correct JSON structure for an authenticated user', function () {
        $user = authenticatedUser();
        createAccommodation($user);

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.accommodations.accommodations.index'))
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Accommodations retrieved successfully',
            ]);
    });

    it('returns only accommodations that belong to the authenticated user', function () {
        $owner  = authenticatedUser();
        $other  = authenticatedUser();

        $ownAccommodation   = createAccommodation($owner);
        $otherAccommodation = createAccommodation($other);

        $response = $this->actingAs($owner, 'sanctum')
            ->getJson(route('api.accommodations.accommodations.index'))
            ->assertStatus(200);

        $ids = collect($response->json('data'))->pluck('id');

        expect($ids)->toContain($ownAccommodation->id)
            ->and($ids)->not->toContain($otherAccommodation->id);
    });

    it('returns an empty data array when the user has no accommodations', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.accommodations.accommodations.index'))
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => [],
            ]);
    });

    it('returns multiple accommodations for a user that owns several', function () {
        $user = authenticatedUser();
        createAccommodation($user);
        createAccommodation($user);
        createAccommodation($user);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.accommodations.accommodations.index'))
            ->assertStatus(200);

        expect(count($response->json('data')))->toBe(3);
    });

    // ── Pagination ─────────────────────────────────────────

    it('paginates results and defaults to 15 per page', function () {
        $user = authenticatedUser();

        // Create 20 accommodations so pagination is exercised
        Accommodation::withoutAuthorization(
            fn () => Accommodation::factory()->count(20)->create(['user_id' => $user->id])
        );

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.accommodations.accommodations.index'))
            ->assertStatus(200);

        // Default per_page = 15, so we should get max 15 items back
        expect(count($response->json('data')))->toBe(15);
    });

    it('respects the per_page query parameter', function () {
        $user = authenticatedUser();
        Accommodation::withoutAuthorization(
            fn () => Accommodation::factory()->count(10)->create(['user_id' => $user->id])
        );

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.accommodations.accommodations.index', ['per_page' => 5]))
            ->assertStatus(200);

        expect(count($response->json('data')))->toBe(5);
    });

    it('returns 422 when per_page is not an integer', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.accommodations.accommodations.index', ['per_page' => 'abc']))
            ->assertStatus(422)
            ->assertJsonFragment(['field' => 'per_page']);
    });

    it('returns 422 when per_page is below minimum (1)', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.accommodations.accommodations.index', ['per_page' => 0]))
            ->assertStatus(422)
            ->assertJsonFragment(['field' => 'per_page']);
    });

    it('returns 422 when per_page exceeds the maximum (100)', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.accommodations.accommodations.index', ['per_page' => 101]))
            ->assertStatus(422)
            ->assertJsonFragment(['field' => 'per_page']);
    });

    it('accepts per_page at boundary values (1 and 100)', function () {
        $user = authenticatedUser();
        createAccommodation($user);

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.accommodations.accommodations.index', ['per_page' => 1]))
            ->assertStatus(200);

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.accommodations.accommodations.index', ['per_page' => 100]))
            ->assertStatus(200);
    });

    // ── Service layer ───────────────────────────────────────

    it('delegates to AccommodationService::getAccommodations with correct user id and per_page', function () {
        $user = authenticatedUser();

        $mock = Mockery::mock(AccommodationService::class);
        $mock->shouldReceive('getAccommodations')
            ->once()
            ->with($user->id, 10)
            ->andReturn(new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10));

        $this->app->instance(AccommodationService::class, $mock);

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.accommodations.accommodations.index', ['per_page' => 10]))
            ->assertStatus(200);
    });

    // ── Response shape ──────────────────────────────────────

    it('response success flag is true on success', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.accommodations.accommodations.index'))
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    });
});

// ============================================================
// SHOW  GET /api/accommodations/{accommodation}
// ============================================================

describe('GET /api/accommodations/{accommodation} (show)', function () {

    it('returns 401 for unauthenticated requests', function () {
        $accommodation = createAccommodation(authenticatedUser());

        $this->getJson(route('api.accommodations.accommodations.show', $accommodation->id))
            ->assertStatus(401);
    });

    it('returns 200 with the accommodation when the owner requests it', function () {
        $user          = authenticatedUser();
        $accommodation = createAccommodation($user);

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.accommodations.accommodations.show', $accommodation->id))
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Accommodation retrieved successfully',
            ])
            ->assertJsonPath('data.id', $accommodation->id);
    });

    it('returns 404 when accommodation does not exist', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.accommodations.accommodations.show', 999999))
            ->assertStatus(404);
    });

    it('returns 404 when user tries to access another owner\'s accommodation', function () {
        $owner         = authenticatedUser();
        $other         = authenticatedUser();
        $accommodation = createAccommodation($owner);

        $this->actingAs($other, 'sanctum')
            ->getJson(route('api.accommodations.accommodations.show', $accommodation->id))
            ->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Accommodation not found',
            ]);
    });

    it('returns correct JSON structure on success', function () {
        $user          = authenticatedUser();
        $accommodation = createAccommodation($user);

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.accommodations.accommodations.show', $accommodation->id))
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                ],
            ]);
    });

    it('response success flag is false on 404', function () {
        $owner         = authenticatedUser();
        $other         = authenticatedUser();
        $accommodation = createAccommodation($owner);

        $this->actingAs($other, 'sanctum')
            ->getJson(route('api.accommodations.accommodations.show', $accommodation->id))
            ->assertStatus(404)
            ->assertJson(['success' => false]);
    });

    // ── Service layer ───────────────────────────────────────

    it('delegates to AccommodationService::getAccommodationById with correct args', function () {
        $user          = authenticatedUser();
        $accommodation = createAccommodation($user);

        $mock = Mockery::mock(AccommodationService::class);
        $mock->shouldReceive('getAccommodationById')
            ->once()
            ->with($user->id, $accommodation->id)
            ->andReturn($accommodation);

        $this->app->instance(AccommodationService::class, $mock);

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.accommodations.accommodations.show', $accommodation->id))
            ->assertStatus(200);
    });

    it('returns 404 when service returns null', function () {
        $user          = authenticatedUser();
        $accommodation = createAccommodation($user);

        $mock = Mockery::mock(AccommodationService::class);
        $mock->shouldReceive('getAccommodationById')
            ->once()
            ->andReturn(null);

        $this->app->instance(AccommodationService::class, $mock);

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.accommodations.accommodations.show', $accommodation->id))
            ->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Accommodation not found',
            ]);
    });

    // ── Soft-deleted / inactive edge cases ─────────────────

    it('returns 404 when the accommodation is soft-deleted', function () {
        $user          = authenticatedUser();
        $accommodation = createAccommodation($user);
        Accommodation::withoutAuthorization(fn () => $accommodation->delete()); // assumes SoftDeletes trait

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.accommodations.accommodations.show', $accommodation->id))
            ->assertStatus(404);
    });
});