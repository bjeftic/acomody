<?php

use App\Models\Currency;

// ============================================================
// POST /api/currency/set
// The currency endpoint stores the selection in the session.
// API routes don't start a session by default, so all requests
// must include Referer/Origin headers to trigger Sanctum's
// EnsureFrontendRequestsAreStateful middleware (which starts the session).
// ============================================================

describe('POST /api/currency/set', function () {

    beforeEach(function () {
        // Stateful headers so Sanctum's middleware starts a session for each request.
        $this->headers = ['Referer' => 'http://localhost', 'Origin' => 'http://localhost'];
    });

    it('returns 200 with currency data for a valid active currency code', function () {
        $this->postJson(route('api.currency.set'), ['currency' => 'USD'], $this->headers)
            ->assertSuccessful()
            ->assertJson([
                'success' => true,
                'message' => 'Currency updated successfully.',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'code',
                    'symbol',
                    'decimal_places',
                    'symbol_position',
                ],
                'meta' => ['previous_currency', 'was_manually_set'],
            ]);
    });

    it('returns the correct currency code in the response data', function () {
        $this->postJson(route('api.currency.set'), ['currency' => 'USD'], $this->headers)
            ->assertSuccessful()
            ->assertJsonPath('data.code', 'USD');
    });

    it('returns was_manually_set = true in meta', function () {
        $this->postJson(route('api.currency.set'), ['currency' => 'USD'], $this->headers)
            ->assertSuccessful()
            ->assertJsonPath('meta.was_manually_set', true);
    });

    it('returns the previous_currency in meta', function () {
        $this->postJson(route('api.currency.set'), ['currency' => 'USD'], $this->headers)
            ->assertSuccessful()
            ->assertJsonStructure(['meta' => ['previous_currency']]);
    });

    it('updates authenticated user preferred_currency', function () {
        $user = authenticatedUser(['preferred_currency' => 'EUR']);

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.currency.set'), ['currency' => 'USD'], $this->headers)
            ->assertSuccessful();

        expect($user->fresh()->preferred_currency)->toBe('USD');
    });

    it('works for guest (unauthenticated) users', function () {
        $this->postJson(route('api.currency.set'), ['currency' => 'GBP'], $this->headers)
            ->assertSuccessful()
            ->assertJsonPath('data.code', 'GBP');
    });

    it('returns 422 for a missing currency field', function () {
        $this->postJson(route('api.currency.set'), [], $this->headers)
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'currency']);
    });

    it('returns 422 for a currency code that does not exist in the database', function () {
        $this->postJson(route('api.currency.set'), ['currency' => 'XXX'], $this->headers)
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'currency']);
    });

    it('returns 422 for a currency code shorter than 3 characters', function () {
        $this->postJson(route('api.currency.set'), ['currency' => 'US'], $this->headers)
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'currency']);
    });

    it('returns 422 for a currency code longer than 3 characters', function () {
        $this->postJson(route('api.currency.set'), ['currency' => 'USDD'], $this->headers)
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'currency']);
    });

    it('returns 422 for an inactive currency code', function () {
        // AUD is in the DB but is_active = false
        $this->postJson(route('api.currency.set'), ['currency' => 'AUD'], $this->headers)
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'currency']);
    });

    it('accepts EUR which is the default base currency', function () {
        $this->postJson(route('api.currency.set'), ['currency' => 'EUR'], $this->headers)
            ->assertSuccessful()
            ->assertJsonPath('data.code', 'EUR');
    });

    it('does not update preferred_currency for guest users', function () {
        $user = authenticatedUser(['preferred_currency' => 'EUR']);

        // Guest request - no actingAs
        $this->postJson(route('api.currency.set'), ['currency' => 'USD'], $this->headers)
            ->assertSuccessful();

        // User's preference in DB should be unchanged
        expect($user->fresh()->preferred_currency)->toBe('EUR');
    });

});
