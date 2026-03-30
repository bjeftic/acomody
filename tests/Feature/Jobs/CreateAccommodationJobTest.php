<?php

use App\Enums\Accommodation\AccommodationOccupation;
use App\Enums\Accommodation\AccommodationType;
use App\Jobs\CreateAccommodation;
use App\Models\Accommodation;
use App\Models\AccommodationDraft;
use App\Models\Amenity;

beforeEach(fn () => seedCurrencyRates());

// ============================================================
// Helpers
// ============================================================

/**
 * Build a minimal but complete draft data payload for job testing.
 *
 * @param  array<string, mixed>  $overrides  Merged into the top-level data array
 */
function makeDraftData(array $overrides = []): array
{
    return array_merge([
        'accommodation_type' => AccommodationType::APARTMENT->value,
        'accommodation_occupation' => AccommodationOccupation::ENTIRE_PLACE->value,
        'title' => ['en' => 'Test Apartment'],
        'description' => ['en' => 'A nice test apartment.'],
        'address' => [
            'country' => 'RS',
            'street' => '123 Main St',
            'city' => 'Belgrade',
            'state' => null,
            'zip_code' => '11000',
        ],
        'coordinates' => ['latitude' => 44.8136, 'longitude' => 20.4489],
        'floor_plan' => [
            'guests' => 4,
            'bedrooms' => 2,
            'bathrooms' => 1,
            'bed_types' => [
                ['bed_type' => 'double', 'quantity' => 2],
                ['bed_type' => 'single', 'quantity' => 1],
            ],
        ],
        'amenities' => [],
        'pricing' => [
            'basePrice' => 50,
            'bookingType' => 'instant_booking',
            'minStay' => 1,
        ],
        'house_rules' => [
            'checkInFrom' => '15:00',
            'checkInUntil' => '20:00',
            'checkOutUntil' => '11:00',
            'hasQuietHours' => false,
            'quietHoursFrom' => '22:00',
            'quietHoursUntil' => '08:00',
            'cancellationPolicy' => 'moderate',
        ],
    ], $overrides);
}

/**
 * Create a waiting-for-approval draft, dispatch the job synchronously,
 * and return the resulting Accommodation.
 *
 * @param  array<string, mixed>  $dataOverrides
 */
function dispatchJobForDraft(array $dataOverrides = []): Accommodation
{
    $user = authenticatedUser();
    $admin = superadmin();
    $location = makeLocation();

    $draft = AccommodationDraft::factory()->create([
        'user_id' => $user->id,
        'status' => 'waiting_for_approval',
        'data' => json_encode(makeDraftData($dataOverrides)),
    ]);

    CreateAccommodation::dispatchSync($draft->id, $location->id, $admin->id);

    return Accommodation::where('accommodation_draft_id', $draft->id)->firstOrFail();
}

// ============================================================
// Accommodation record
// ============================================================

describe('CreateAccommodation job — accommodation record', function () {

    it('creates an accommodation record from the draft', function () {
        $user = authenticatedUser();
        $admin = superadmin();
        $location = makeLocation();

        $draft = AccommodationDraft::factory()->create([
            'user_id' => $user->id,
            'status' => 'waiting_for_approval',
            'data' => json_encode(makeDraftData()),
        ]);

        CreateAccommodation::dispatchSync($draft->id, $location->id, $admin->id);

        $accommodation = Accommodation::where('accommodation_draft_id', $draft->id)->firstOrFail();

        expect($accommodation->location_id)->toBe($location->id)
            ->and($accommodation->user_id)->toBe($user->id)
            ->and($accommodation->getTranslation('title', 'en'))->toBe('Test Apartment')
            ->and($accommodation->max_guests)->toBe(4)
            ->and($accommodation->bedrooms)->toBe(2)
            ->and($accommodation->bathrooms)->toBe(1);
    });

    it('sets the draft status to published after completion', function () {
        $user = authenticatedUser();
        $admin = superadmin();
        $location = makeLocation();

        $draft = AccommodationDraft::factory()->create([
            'user_id' => $user->id,
            'status' => 'waiting_for_approval',
            'data' => json_encode(makeDraftData()),
        ]);

        CreateAccommodation::dispatchSync($draft->id, $location->id, $admin->id);

        $this->assertDatabaseHas('accommodation_drafts', [
            'id' => $draft->id,
            'status' => 'published',
        ]);
    });

    it('maps house_rules check-in and check-out times correctly', function () {
        $accommodation = dispatchJobForDraft();

        expect($accommodation->check_in_from)->toBe('15:00:00')
            ->and($accommodation->check_in_until)->toBe('20:00:00')
            ->and($accommodation->check_out_until)->toBe('11:00:00');
    });

    it('sets approved_by to the dispatching admin id', function () {
        $user = authenticatedUser();
        $admin = superadmin();
        $location = makeLocation();

        $draft = AccommodationDraft::factory()->create([
            'user_id' => $user->id,
            'status' => 'waiting_for_approval',
            'data' => json_encode(makeDraftData()),
        ]);

        CreateAccommodation::dispatchSync($draft->id, $location->id, $admin->id);

        $accommodation = Accommodation::where('accommodation_draft_id', $draft->id)->first();

        expect($accommodation->approved_by)->toBe($admin->id);
    });
});

// ============================================================
// Bed types
// ============================================================

describe('CreateAccommodation job — bed types', function () {

    it('creates accommodation_beds records from floor_plan bed_types', function () {
        $accommodation = dispatchJobForDraft([
            'floor_plan' => [
                'guests' => 2,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'bed_types' => [
                    ['bed_type' => 'double', 'quantity' => 1],
                    ['bed_type' => 'single', 'quantity' => 2],
                ],
            ],
        ]);

        expect($accommodation->beds()->count())->toBe(2);

        $this->assertDatabaseHas('accommodation_beds', [
            'accommodation_id' => $accommodation->id,
            'bed_type' => 'double',
            'quantity' => 1,
        ]);

        $this->assertDatabaseHas('accommodation_beds', [
            'accommodation_id' => $accommodation->id,
            'bed_type' => 'single',
            'quantity' => 2,
        ]);
    });

    it('filters out bed types with zero quantity', function () {
        $accommodation = dispatchJobForDraft([
            'floor_plan' => [
                'guests' => 2,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'bed_types' => [
                    ['bed_type' => 'king', 'quantity' => 0],
                    ['bed_type' => 'double', 'quantity' => 1],
                ],
            ],
        ]);

        expect($accommodation->beds()->count())->toBe(1);

        $this->assertDatabaseMissing('accommodation_beds', [
            'accommodation_id' => $accommodation->id,
            'bed_type' => 'king',
        ]);
    });

    it('creates no bed records when all bed types have zero quantity', function () {
        $accommodation = dispatchJobForDraft([
            'floor_plan' => [
                'guests' => 2,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'bed_types' => [
                    ['bed_type' => 'king', 'quantity' => 0],
                    ['bed_type' => 'queen', 'quantity' => 0],
                ],
            ],
        ]);

        expect($accommodation->beds()->count())->toBe(0);
    });

    it('creates no bed records when floor_plan has no bed_types key', function () {
        $accommodation = dispatchJobForDraft([
            'floor_plan' => [
                'guests' => 2,
                'bedrooms' => 1,
                'bathrooms' => 1,
            ],
        ]);

        expect($accommodation->beds()->count())->toBe(0);
    });
});

// ============================================================
// Amenities
// ============================================================

describe('CreateAccommodation job — amenities', function () {

    it('syncs amenities from the draft data', function () {
        $amenityIds = Amenity::inRandomOrder()->limit(3)->pluck('id')->toArray();

        $accommodation = dispatchJobForDraft(['amenities' => $amenityIds]);

        expect($accommodation->amenities()->count())->toBe(3);

        foreach ($amenityIds as $id) {
            $this->assertDatabaseHas('accommodation_amenity', [
                'accommodation_id' => $accommodation->id,
                'amenity_id' => $id,
            ]);
        }
    });

    it('skips amenity sync when amenities array is empty', function () {
        $accommodation = dispatchJobForDraft(['amenities' => []]);

        expect($accommodation->amenities()->count())->toBe(0);
    });
});

// ============================================================
// Pricing
// ============================================================

describe('CreateAccommodation job — pricing', function () {

    it('creates a priceable_item record with the correct base price', function () {
        $accommodation = dispatchJobForDraft([
            'pricing' => ['basePrice' => 75, 'bookingType' => 'instant_booking', 'minStay' => 1],
        ]);

        $this->assertDatabaseHas('priceable_items', [
            'priceable_type' => Accommodation::class,
            'priceable_id' => $accommodation->id,
            'base_price' => 75,
        ]);
    });
});
