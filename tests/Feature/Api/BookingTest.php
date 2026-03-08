<?php

use App\Enums\Booking\BookingStatus;
use App\Services\BookingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;

// ============================================================
// GET /api/bookings (index)
// ============================================================

describe('GET /api/bookings (guest index)', function () {

    it('returns 401 for unauthenticated requests', function () {
        $this->getJson(route('api.bookings.index'))
            ->assertUnauthorized();
    });

    it('returns 200 with correct JSON structure', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.bookings.index'))
            ->assertSuccessful()
            ->assertJson(['success' => true, 'message' => 'Bookings retrieved successfully'])
            ->assertJsonStructure(['success', 'message', 'data', 'meta']);
    });

    it('returns an empty data array when user has no bookings', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.bookings.index'))
            ->assertSuccessful()
            ->assertJson(['data' => []]);
    });

    it('returns only the authenticated guest\'s bookings', function () {
        $guest = authenticatedUser();
        createBooking($guest, authenticatedUser());

        $this->actingAs($guest, 'sanctum')
            ->getJson(route('api.bookings.index'))
            ->assertSuccessful()
            ->assertJsonCount(1, 'data');
    });

    it('returns pagination meta', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.bookings.index'))
            ->assertSuccessful()
            ->assertJsonStructure(['meta' => ['current_page', 'last_page', 'per_page', 'total']]);
    });
});

// ============================================================
// GET /api/bookings/{booking} (show)
// ============================================================

describe('GET /api/bookings/{booking} (guest show)', function () {

    it('returns 401 for unauthenticated requests', function () {
        $booking = createBooking(authenticatedUser(), authenticatedUser());

        Auth::logout();

        $this->getJson(route('api.bookings.show', $booking))
            ->assertUnauthorized();
    });

    it('returns 200 when the guest owns the booking', function () {
        $guest = authenticatedUser();
        $booking = createBooking($guest, authenticatedUser());

        $this->actingAs($guest, 'sanctum')
            ->getJson(route('api.bookings.show', $booking))
            ->assertSuccessful()
            ->assertJson(['success' => true, 'message' => 'Booking retrieved successfully']);
    });

    it('returns 403 when another user tries to access the booking', function () {
        $booking = createBooking(authenticatedUser(), authenticatedUser());
        $otherGuest = authenticatedUser();

        $this->actingAs($otherGuest, 'sanctum')
            ->getJson(route('api.bookings.show', $booking))
            ->assertForbidden();
    });

    it('returns correct JSON structure on success', function () {
        $guest = authenticatedUser();
        $booking = createBooking($guest, authenticatedUser());

        $this->actingAs($guest, 'sanctum')
            ->getJson(route('api.bookings.show', $booking))
            ->assertSuccessful()
            ->assertJsonStructure(['success', 'message', 'data']);
    });

    it('returns 404 for a non-existent booking id', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.bookings.show', 'non-existent-id'))
            ->assertNotFound();
    });
});

// ============================================================
// POST /api/bookings (store)
// ============================================================

describe('POST /api/bookings (store)', function () {

    it('returns 401 for unauthenticated requests', function () {
        $this->postJson(route('api.bookings.store'), [])
            ->assertUnauthorized();
    });

    it('returns 422 when accommodation_id is missing', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.bookings.store'), [
                'check_in' => now()->addDays(5)->toDateString(),
                'check_out' => now()->addDays(8)->toDateString(),
                'guests' => 2,
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'accommodation_id']);
    });

    it('returns 422 when check_in is missing', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.bookings.store'), [
                'accommodation_id' => $accommodation->id,
                'check_out' => now()->addDays(8)->toDateString(),
                'guests' => 2,
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'check_in']);
    });

    it('returns 422 when check_out is missing', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.bookings.store'), [
                'accommodation_id' => $accommodation->id,
                'check_in' => now()->addDays(5)->toDateString(),
                'guests' => 2,
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'check_out']);
    });

    it('returns 422 when check_in is in the past', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.bookings.store'), [
                'accommodation_id' => $accommodation->id,
                'check_in' => now()->subDay()->toDateString(),
                'check_out' => now()->addDays(3)->toDateString(),
                'guests' => 2,
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'check_in']);
    });

    it('returns 422 when check_out is before check_in', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.bookings.store'), [
                'accommodation_id' => $accommodation->id,
                'check_in' => now()->addDays(8)->toDateString(),
                'check_out' => now()->addDays(5)->toDateString(),
                'guests' => 2,
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'check_out']);
    });

    it('returns 422 when guests is missing', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.bookings.store'), [
                'accommodation_id' => $accommodation->id,
                'check_in' => now()->addDays(5)->toDateString(),
                'check_out' => now()->addDays(8)->toDateString(),
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'guests']);
    });

    it('returns 404 when the accommodation is not active', function () {
        $guest = authenticatedUser();
        $host = authenticatedUser();
        $accommodation = createAccommodation($host, ['is_active' => false]);

        $this->actingAs($guest, 'sanctum')
            ->postJson(route('api.bookings.store'), [
                'accommodation_id' => $accommodation->id,
                'check_in' => now()->addDays(5)->toDateString(),
                'check_out' => now()->addDays(8)->toDateString(),
                'guests' => 2,
            ])
            ->assertNotFound();
    });

    it('returns 409 when the accommodation is unavailable', function () {
        $guest = authenticatedUser();
        $host = authenticatedUser();
        $accommodation = createAccommodation($host);

        $mock = Mockery::mock(BookingService::class);
        $mock->shouldReceive('createBooking')
            ->once()
            ->andThrow(new \RuntimeException('Dates are not available'));

        $this->app->instance(BookingService::class, $mock);

        $this->actingAs($guest, 'sanctum')
            ->postJson(route('api.bookings.store'), [
                'accommodation_id' => $accommodation->id,
                'check_in' => now()->addDays(5)->toDateString(),
                'check_out' => now()->addDays(8)->toDateString(),
                'guests' => 2,
            ])
            ->assertStatus(409);
    });

    it('returns 201 when BookingService creates the booking successfully', function () {
        $guest = authenticatedUser();
        $host = authenticatedUser();
        $accommodation = createAccommodation($host);
        $booking = createBooking($guest, $host);

        $mock = Mockery::mock(BookingService::class);
        $mock->shouldReceive('createBooking')
            ->once()
            ->andReturn($booking);
        $mock->shouldReceive('fetchBooking')
            ->once()
            ->andReturn($booking);

        $this->app->instance(BookingService::class, $mock);

        $this->actingAs($guest, 'sanctum')
            ->postJson(route('api.bookings.store'), [
                'accommodation_id' => $accommodation->id,
                'check_in' => now()->addDays(5)->toDateString(),
                'check_out' => now()->addDays(8)->toDateString(),
                'guests' => 2,
            ])
            ->assertCreated()
            ->assertJson(['success' => true]);
    });
});

// ============================================================
// POST /api/bookings/{booking}/cancel (cancel)
// ============================================================

describe('POST /api/bookings/{booking}/cancel (guest cancel)', function () {

    it('returns 401 for unauthenticated requests', function () {
        $booking = createBooking(authenticatedUser(), authenticatedUser());

        Auth::logout();

        $this->postJson(route('api.bookings.cancel', $booking))
            ->assertUnauthorized();
    });

    it('returns 200 when the guest cancels their own pending booking', function () {
        Event::fake();

        $guest = authenticatedUser();
        $booking = createBooking($guest, authenticatedUser());

        $this->actingAs($guest, 'sanctum')
            ->postJson(route('api.bookings.cancel', $booking), [])
            ->assertSuccessful()
            ->assertJson(['success' => true, 'message' => 'Booking cancelled successfully']);
    });

    it('returns 403 when a different user tries to cancel the booking', function () {
        $booking = createBooking(authenticatedUser(), authenticatedUser());
        $otherUser = authenticatedUser();

        $this->actingAs($otherUser, 'sanctum')
            ->postJson(route('api.bookings.cancel', $booking), [])
            ->assertForbidden();
    });

    it('returns 409 when the booking cannot be cancelled', function () {
        Event::fake();

        $guest = authenticatedUser();

        $booking = createBooking($guest, authenticatedUser(), ['status' => BookingStatus::CANCELLED]);
        $guest = $booking->fresh()->guest;

        $this->actingAs($guest, 'sanctum')
            ->postJson(route('api.bookings.cancel', $booking), [])
            ->assertStatus(409);
    });

    it('returns 200 when the guest cancels their own confirmed booking', function () {
        Event::fake();

        $guest = authenticatedUser();
        $booking = createBooking($guest, authenticatedUser(), ['status' => BookingStatus::CONFIRMED]);

        $this->actingAs($guest, 'sanctum')
            ->postJson(route('api.bookings.cancel', $booking), [])
            ->assertSuccessful()
            ->assertJson(['success' => true, 'message' => 'Booking cancelled successfully']);
    });

    it('stores the cancellation reason on the booking', function () {
        Event::fake();

        $guest = authenticatedUser();
        $booking = createBooking($guest, authenticatedUser());

        $this->actingAs($guest, 'sanctum')
            ->postJson(route('api.bookings.cancel', $booking), ['reason' => 'Change of plans']);

        expect($booking->fresh()->cancellation_reason)->toBe('Change of plans');
    });

    it('sets cancelled_at and cancelled_by_user_id after guest cancellation', function () {
        Event::fake();

        $guest = authenticatedUser();
        $booking = createBooking($guest, authenticatedUser());

        $this->actingAs($guest, 'sanctum')
            ->postJson(route('api.bookings.cancel', $booking), []);

        $fresh = $booking->fresh();
        expect($fresh->cancelled_at)->not->toBeNull();
        expect($fresh->cancelled_by_user_id)->toBe($guest->id);
    });

    it('returns the booking with cancelled status in the response data', function () {
        Event::fake();

        $guest = authenticatedUser();
        $booking = createBooking($guest, authenticatedUser());

        $this->actingAs($guest, 'sanctum')
            ->postJson(route('api.bookings.cancel', $booking), [])
            ->assertSuccessful()
            ->assertJsonPath('data.status', 'cancelled');
    });

    it('returns 409 when trying to cancel a declined booking', function () {
        $guest = authenticatedUser();
        $booking = createBooking($guest, authenticatedUser(), ['status' => BookingStatus::DECLINED]);

        $this->actingAs($guest, 'sanctum')
            ->postJson(route('api.bookings.cancel', $booking), [])
            ->assertStatus(409);
    });

    it('returns 409 when trying to cancel a completed booking', function () {
        $guest = authenticatedUser();
        $booking = createBooking($guest, authenticatedUser(), ['status' => BookingStatus::COMPLETED]);

        $this->actingAs($guest, 'sanctum')
            ->postJson(route('api.bookings.cancel', $booking), [])
            ->assertStatus(409);
    });

    it('returns 422 when reason exceeds 500 characters', function () {
        $guest = authenticatedUser();
        $booking = createBooking($guest, authenticatedUser());

        $this->actingAs($guest, 'sanctum')
            ->postJson(route('api.bookings.cancel', $booking), ['reason' => str_repeat('a', 501)])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'reason']);
    });
});
