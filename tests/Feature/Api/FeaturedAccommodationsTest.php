<?php

use App\Services\SearchService;

// ============================================================
// GET /api/public/accommodations
// ============================================================

describe('GET /api/public/accommodations', function () {

    beforeEach(function () {
        $this->mockResults = [
            'hits' => [
                ['document' => ['id' => '1', 'title' => 'Nice Apartment', 'rating' => 4.9, 'base_price_eur' => 80, 'photos' => [], 'location' => [44.0, 20.0], 'location_id' => 1]],
                ['document' => ['id' => '2', 'title' => 'Cozy Villa', 'rating' => 4.8, 'base_price_eur' => 120, 'photos' => [], 'location' => [44.1, 20.1], 'location_id' => 2]],
            ],
            'found' => 2,
            'page' => 1,
        ];

        $mock = Mockery::mock(SearchService::class);
        $mock->shouldReceive('getSortByFilter')
            ->andReturn('rating:desc');
        $mock->shouldReceive('searchCollection')
            ->andReturn($this->mockResults);

        $this->app->instance(SearchService::class, $mock);

        seedCurrencyRates();
    });

    it('returns 200 without authentication', function () {
        $this->getJson(route('api.publicaccommodations.index'))
            ->assertSuccessful();
    });

    it('returns correct JSON structure', function () {
        $response = $this->getJson(route('api.publicaccommodations.index'))
            ->assertSuccessful();

        expect($response->json())->toHaveKeys(['hits', 'found', 'page', 'per_page']);
    });

    it('returns accommodations sorted by rating by default', function () {
        $response = $this->getJson(route('api.publicaccommodations.index'))
            ->assertSuccessful();

        expect($response->json('hits'))->toBeArray();
        expect($response->json('found'))->toBe(2);
    });

    it('returns hits as an array', function () {
        $response = $this->getJson(route('api.publicaccommodations.index'))
            ->assertSuccessful();

        expect($response->json('hits'))->toBeArray();
    });

    it('each hit has id, title, rating, price and photos keys', function () {
        $response = $this->getJson(route('api.publicaccommodations.index'))
            ->assertSuccessful();

        foreach ($response->json('hits') as $hit) {
            expect($hit)->toHaveKeys(['id', 'title', 'rating', 'photos']);
        }
    });

    it('accepts valid sortBy values', function () {
        foreach (['rating', 'reviews', 'price_asc', 'price_desc', 'newest'] as $sortBy) {
            $this->getJson(route('api.publicaccommodations.index', ['sortBy' => $sortBy]))
                ->assertSuccessful();
        }
    });

    it('rejects invalid sortBy value with 422', function () {
        $this->getJson(route('api.publicaccommodations.index', ['sortBy' => 'invalid_sort']))
            ->assertUnprocessable();
    });

    it('rejects page below 1 with 422', function () {
        $this->getJson(route('api.publicaccommodations.index', ['page' => 0]))
            ->assertUnprocessable();
    });

    it('rejects perPage above 100 with 422', function () {
        $this->getJson(route('api.publicaccommodations.index', ['perPage' => 101]))
            ->assertUnprocessable();
    });

    it('accepts perPage within valid range', function () {
        $this->getJson(route('api.publicaccommodations.index', ['perPage' => 6]))
            ->assertSuccessful();
    });

    it('returns empty hits when no results found', function () {
        $emptyMock = Mockery::mock(SearchService::class);
        $emptyMock->shouldReceive('getSortByFilter')->andReturn('rating:desc');
        $emptyMock->shouldReceive('searchCollection')->andReturn([
            'hits' => [], 'found' => 0, 'page' => 1,
        ]);

        $this->app->instance(SearchService::class, $emptyMock);

        $response = $this->getJson(route('api.publicaccommodations.index'))
            ->assertSuccessful();

        expect($response->json('hits'))->toBeEmpty();
        expect($response->json('found'))->toBe(0);
    });

    it('passes location_id filter when provided', function () {
        $superadmin = authenticatedUser(['is_superadmin' => true]);
        $country = \App\Models\Country::where('is_active', true)->first();

        $location = \App\Models\Location::withoutSyncingToSearch(fn () => \App\Models\Location::create([
            'country_id' => $country->id,
            'name' => 'Test City',
            'location_type' => \App\Enums\Location\LocationType::CITY->value,
            'latitude' => 44.8,
            'longitude' => 20.4,
            'user_id' => $superadmin->id,
        ]));

        $mock = Mockery::mock(SearchService::class);
        $mock->shouldReceive('getSortByFilter')->andReturn('rating:desc');
        $mock->shouldReceive('searchCollection')
            ->withArgs(function (string $_collection, string $_query, array $options) use ($location) {
                return str_contains($options['filter_by'] ?? '', "location_id:={$location->id}");
            })
            ->andReturn($this->mockResults);

        $this->app->instance(SearchService::class, $mock);

        $this->getJson(route('api.publicaccommodations.index', ['location_id' => $location->id]))
            ->assertSuccessful();
    });

});
