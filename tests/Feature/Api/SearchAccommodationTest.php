<?php

use App\Services\SearchService;

// ============================================================
// GET /api/search/accommodations — validation
// ============================================================

describe('GET /api/search/accommodations validation', function () {
    it('rejects a request with no location or bounds', function () {
        $this->getJson('/api/search/accommodations')
            ->assertStatus(422)
            ->assertJsonFragment(['field' => 'location.id']);
    });

    it('accepts a ULID string as location.id', function () {
        $location = makeLocation();

        $this->mock(SearchService::class, function ($mock) {
            $mock->shouldReceive('getSortByFilter')->andReturn('base_price_eur:asc');
            $mock->shouldReceive('searchCollection')->andReturn([
                'found' => 0,
                'hits' => [],
                'page' => 1,
                'facet_counts' => [],
            ]);
        });

        $this->getJson('/api/search/accommodations?' . http_build_query([
            'location' => ['id' => $location->id, 'name' => $location->name],
        ]))->assertStatus(200);
    });

    it('rejects a request where checkOut is before checkIn', function () {
        $location = makeLocation();

        $this->getJson('/api/search/accommodations?' . http_build_query([
            'location' => ['id' => $location->id, 'name' => $location->name],
            'checkIn' => '2026-03-15',
            'checkOut' => '2026-03-10',
        ]))->assertStatus(422)
            ->assertJsonFragment(['field' => 'checkOut']);
    });

    it('accepts valid bounds instead of location', function () {
        $this->mock(SearchService::class, function ($mock) {
            $mock->shouldReceive('getSortByFilter')->andReturn('base_price_eur:asc');
            $mock->shouldReceive('searchCollection')->andReturn([
                'found' => 0,
                'hits' => [],
                'page' => 1,
                'facet_counts' => [],
            ]);
        });

        $this->getJson('/api/search/accommodations?' . http_build_query([
            'bounds' => [
                'northEast' => ['lat' => 45.0, 'lng' => 21.0],
                'southWest' => ['lat' => 44.0, 'lng' => 20.0],
            ],
        ]))->assertStatus(200);
    });
});
