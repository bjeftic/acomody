<?php

use App\Models\Accommodation;
use App\Services\AccommodationService;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

// ============================================================
// INDEX  GET /api/accommodations
// ============================================================

describe('GET /api/accommodations (index)', function () {

    beforeEach(function () {
        seedCurrencyRates();
    });

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
        $owner = authenticatedUser();
        $other = authenticatedUser();

        $ownerAccommodation = createAccommodation($owner);
        $otherAccommodation = createAccommodation($other);

        $response = $this->actingAs($owner, 'sanctum')
            ->getJson(route('api.accommodations.accommodations.index'))
            ->assertStatus(200);

        $ids = collect($response->json('data'))->pluck('id');

        expect($ids)->toContain($ownerAccommodation->id)
            ->and($ids)->not->toContain($otherAccommodation->id);
    });

    it('returns an empty data array when the user has no accommodations', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.accommodations.accommodations.index'))
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [],
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
        for ($i = 0; $i < 20; $i++) {
            createAccommodation($user);
        }

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.accommodations.accommodations.index'))
            ->assertStatus(200);

        // Default per_page = 15, so we should get max 15 items back
        expect(count($response->json('data')))->toBe(15);
    });

    it('respects the per_page query parameter', function () {
        $user = authenticatedUser();
        for ($i = 0; $i < 10; $i++) {
            createAccommodation($user);
        }

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

    beforeEach(function () {
        seedCurrencyRates();
    });

    it('returns 401 for unauthenticated requests', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        Auth::logout();

        $this->getJson(route('api.accommodations.accommodations.show', $accommodation->id))
            ->assertStatus(401);
    });

    it('returns 200 with the accommodation when the owner requests it', function () {
        $user = authenticatedUser();
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
        $owner = authenticatedUser();
        $other = authenticatedUser();
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
        $user = authenticatedUser();
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
        $owner = authenticatedUser();
        $other = authenticatedUser();
        $accommodation = createAccommodation($owner);

        $this->actingAs($other, 'sanctum')
            ->getJson(route('api.accommodations.accommodations.show', $accommodation->id))
            ->assertStatus(404)
            ->assertJson(['success' => false]);
    });

    // ── Service layer ───────────────────────────────────────

    it('delegates to AccommodationService::getAccommodationForEdit with correct args', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        $mock = Mockery::mock(AccommodationService::class);
        $mock->shouldReceive('getAccommodationForEdit')
            ->once()
            ->with($accommodation->id)
            ->andReturn($accommodation);

        $this->app->instance(AccommodationService::class, $mock);

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.accommodations.accommodations.show', $accommodation->id))
            ->assertStatus(200);
    });

    it('returns 404 when service returns null', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        $mock = Mockery::mock(AccommodationService::class);
        $mock->shouldReceive('getAccommodationForEdit')
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
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);
        $accommodation->delete(); // assumes SoftDeletes trait

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.accommodations.accommodations.show', $accommodation->id))
            ->assertStatus(404);
    });
});

// ============================================================
// CHECK AVAILABILITY  POST /api/accommodations/{id}/check-availability
// ============================================================

describe('POST /api/accommodations/{id}/check-availability', function () {

    it('returns 401 for unauthenticated requests', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        Auth::logout();

        $this->postJson(route('api.accommodations.accommodations.check-availability', $accommodation), [])
            ->assertUnauthorized();
    });

    it('returns 422 when check_in is missing', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.accommodations.accommodations.check-availability', $accommodation), [
                'check_out' => now()->addDays(5)->toDateString(),
                'guests' => 2,
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'check_in']);
    });

    it('returns 422 when check_out is missing', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.accommodations.accommodations.check-availability', $accommodation), [
                'check_in' => now()->addDays(2)->toDateString(),
                'guests' => 2,
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'check_out']);
    });

    it('returns 422 when guests is missing', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.accommodations.accommodations.check-availability', $accommodation), [
                'check_in' => now()->addDays(2)->toDateString(),
                'check_out' => now()->addDays(5)->toDateString(),
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'guests']);
    });

    it('returns 422 when check_in is in the past', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.accommodations.accommodations.check-availability', $accommodation), [
                'check_in' => now()->subDay()->toDateString(),
                'check_out' => now()->addDays(3)->toDateString(),
                'guests' => 2,
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'check_in']);
    });

    it('returns 200 and delegates to BookingService::checkAvailability', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        $mock = Mockery::mock(BookingService::class);
        $mock->shouldReceive('checkAvailability')
            ->once()
            ->andReturn(['available' => true, 'reasons' => []]);

        $this->app->instance(BookingService::class, $mock);

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.accommodations.accommodations.check-availability', $accommodation), [
                'check_in' => now()->addDays(2)->toDateString(),
                'check_out' => now()->addDays(5)->toDateString(),
                'guests' => 2,
            ])
            ->assertSuccessful()
            ->assertJson(['success' => true, 'message' => 'Availability checked']);
    });

});

// ============================================================
// CALCULATE PRICE  POST /api/accommodations/{id}/calculate-price
// ============================================================

describe('POST /api/accommodations/{id}/calculate-price', function () {

    beforeEach(fn () => seedCurrencyRates());

    it('returns 401 for unauthenticated requests', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        Auth::logout();

        $this->postJson(route('api.accommodations.accommodations.calculate-price', $accommodation), [])
            ->assertUnauthorized();
    });

    it('returns 422 when check_in is missing', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.accommodations.accommodations.calculate-price', $accommodation), [
                'check_out' => now()->addDays(5)->toDateString(),
                'guests' => 2,
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'check_in']);
    });

    it('returns 422 when check_out is missing', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.accommodations.accommodations.calculate-price', $accommodation), [
                'check_in' => now()->addDays(2)->toDateString(),
                'guests' => 2,
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'check_out']);
    });

    it('returns 422 when guests is missing', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.accommodations.accommodations.calculate-price', $accommodation), [
                'check_in' => now()->addDays(2)->toDateString(),
                'check_out' => now()->addDays(5)->toDateString(),
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'guests']);
    });

    it('returns 200 and delegates to BookingService::calculatePrice', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        $mock = Mockery::mock(BookingService::class);
        $mock->shouldReceive('calculatePrice')
            ->once()
            ->andReturn([
                'subtotal' => 300.00,
                'total' => 300.00,
                'currency' => 'EUR',
                'nights' => 3,
                'priceable_item_id' => null,
            ]);

        $this->app->instance(BookingService::class, $mock);

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.accommodations.accommodations.calculate-price', $accommodation), [
                'check_in' => now()->addDays(2)->toDateString(),
                'check_out' => now()->addDays(5)->toDateString(),
                'guests' => 2,
            ])
            ->assertSuccessful()
            ->assertJson(['success' => true, 'message' => 'Price calculated']);
    });

});

// ============================================================
// PUT /api/accommodations/{id} (update)
// ============================================================

describe('PATCH /api/accommodations/{id} (update)', function () {

    beforeEach(function () {
        seedCurrencyRates();
    });

    /** @return array<string, mixed> */
    function validAccommodationPayload(): array
    {
        return [
            'accommodation_type' => 'apartment',
            'accommodation_occupation' => 'entire_place',
            'address' => ['street' => '123 Main St'],
            'coordinates' => ['latitude' => 44.8, 'longitude' => 20.4],
            'floor_plan' => [
                'guests' => 4,
                'bedrooms' => 2,
                'bathrooms' => 1,
                'bed_types' => [
                    ['bed_type' => 'double', 'quantity' => 2],
                ],
            ],
            'amenities' => [],
            'title' => ['en' => 'Cozy Apartment in Belgrade'],
            'description' => ['en' => 'A beautiful and cozy apartment located in the heart of Belgrade, perfect for any traveler.'],
            'pricing' => [
                'basePrice' => 75,
                'bookingType' => 'instant_booking',
                'minStay' => 1,
            ],
            'house_rules' => [
                'checkInFrom' => '15:00',
                'checkInUntil' => '20:00',
                'checkOutUntil' => '11:00',
                'hasQuietHours' => false,
                'quietHoursFrom' => null,
                'quietHoursUntil' => null,
                'cancellationPolicy' => 'moderate',
            ],
        ];
    }

    it('returns 401 for unauthenticated requests', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        Auth::logout();

        $this->patchJson(route('api.accommodations.accommodations.update', $accommodation), validAccommodationPayload())
            ->assertUnauthorized();
    });

    it('returns 403 when a different user tries to update', function () {
        $owner = authenticatedUser();
        $accommodation = createAccommodation($owner);

        $other = authenticatedUser();

        $this->actingAs($other, 'sanctum')
            ->patchJson(route('api.accommodations.accommodations.update', $accommodation), validAccommodationPayload())
            ->assertForbidden();
    });

    it('returns 200 on success', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        $this->actingAs($user, 'sanctum')
            ->patchJson(route('api.accommodations.accommodations.update', $accommodation), validAccommodationPayload())
            ->assertSuccessful()
            ->assertJson(['success' => true, 'message' => 'Accommodation updated successfully']);
    });

    it('persists updated title and description', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        $this->actingAs($user, 'sanctum')
            ->patchJson(route('api.accommodations.accommodations.update', $accommodation), validAccommodationPayload());

        $accommodation->refresh();
        expect($accommodation->getTranslation('title', 'en'))->toBe('Cozy Apartment in Belgrade');
    });

    it('returns 422 when title is missing', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        $payload = validAccommodationPayload();
        unset($payload['title']);

        $this->actingAs($user, 'sanctum')
            ->patchJson(route('api.accommodations.accommodations.update', $accommodation), $payload)
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'title']);
    });

    it('returns 422 when accommodation_type is invalid', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        $payload = validAccommodationPayload();
        $payload['accommodation_type'] = 'spaceship';

        $this->actingAs($user, 'sanctum')
            ->patchJson(route('api.accommodations.accommodations.update', $accommodation), $payload)
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'accommodation_type']);
    });

    it('returns 422 when cancellation_policy is invalid', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        $payload = validAccommodationPayload();
        $payload['house_rules']['cancellationPolicy'] = 'super_strict';

        $this->actingAs($user, 'sanctum')
            ->patchJson(route('api.accommodations.accommodations.update', $accommodation), $payload)
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'house_rules.cancellationPolicy']);
    });

    it('syncs amenities on update', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);
        $amenity = \App\Models\Amenity::factory()->create();

        $payload = validAccommodationPayload();
        $payload['amenities'] = [$amenity->id];

        $this->actingAs($user, 'sanctum')
            ->patchJson(route('api.accommodations.accommodations.update', $accommodation), $payload)
            ->assertSuccessful();

        $this->assertDatabaseHas('accommodation_amenity', [
            'accommodation_id' => $accommodation->id,
            'amenity_id' => $amenity->id,
        ]);
    });

    it('recreates beds on update', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        $payload = validAccommodationPayload();

        $this->actingAs($user, 'sanctum')
            ->patchJson(route('api.accommodations.accommodations.update', $accommodation), $payload)
            ->assertSuccessful();

        $this->assertDatabaseHas('accommodation_beds', [
            'accommodation_id' => $accommodation->id,
            'bed_type' => 'double',
            'quantity' => 2,
        ]);
    });
});
