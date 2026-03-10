<?php

use App\Models\AccommodationDraft;
use App\Models\Photo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

// ============================================================
// AccommodationDraftController
// All routes are under /api/accommodation-drafts (auth:sanctum)
//
// beforeEach creates a user and an owned 'draft'-status draft
// so every test has a ready-made draft to work with.
// ============================================================

beforeEach(function () {
    $this->user = authenticatedUser();
    $this->draft = AccommodationDraft::factory()->create([
        'user_id' => $this->user->id,
        'status' => 'draft',
    ]);
});

// ============================================================
// GET /api/accommodation-drafts (index)
// ============================================================

describe('GET /api/accommodation-drafts (index)', function () {

    it('returns 401 for unauthenticated requests', function () {
        Auth::logout();
        $this->getJson(route('api.accommodation.drafts.accommodation-draft.index'))
            ->assertUnauthorized();
    });

    it('returns 200 with drafts for the authenticated user', function () {
        $this->actingAs($this->user, 'sanctum')
            ->getJson(route('api.accommodation.drafts.accommodation-draft.index'))
            ->assertSuccessful()
            ->assertJson(['success' => true, 'message' => 'Accommodation drafts retrieved successfully']);
    });

    it('defaults to returning drafts with status=draft', function () {
        $this->actingAs($this->user, 'sanctum')
            ->getJson(route('api.accommodation.drafts.accommodation-draft.index'))
            ->assertSuccessful()
            ->assertJsonCount(1, 'data');
    });

    it('returns only drafts belonging to the authenticated user', function () {
        // Create a draft for another user
        $other = authenticatedUser();
        AccommodationDraft::factory()->create(['user_id' => $other->id, 'status' => 'draft']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson(route('api.accommodation.drafts.accommodation-draft.index'))
            ->assertSuccessful();

        expect(count($response->json('data')))->toBe(1);
    });

    it('filters drafts by status=waiting_for_approval', function () {
        AccommodationDraft::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'waiting_for_approval',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson(route('api.accommodation.drafts.accommodation-draft.index', ['status' => 'waiting_for_approval']))
            ->assertSuccessful();

        expect(count($response->json('data')))->toBe(1);
    });

    it('returns 422 for an invalid status value', function () {
        $this->actingAs($this->user, 'sanctum')
            ->getJson(route('api.accommodation.drafts.accommodation-draft.index', ['status' => 'invalid']))
            ->assertUnprocessable();
    });
});

// ============================================================
// GET /api/accommodation-drafts/draft (getAccommodationDraft)
// ============================================================

describe('GET /api/accommodation-drafts/draft (getAccommodationDraft)', function () {

    it('returns 401 for unauthenticated requests', function () {
        Auth::logout();
        $this->getJson(route('api.accommodation.drafts.accommodation-draft.get'))
            ->assertUnauthorized();
    });

    it('returns 200 with the draft when the user has one', function () {
        $this->actingAs($this->user, 'sanctum')
            ->getJson(route('api.accommodation.drafts.accommodation-draft.get'))
            ->assertSuccessful()
            ->assertJson(['success' => true, 'message' => 'Accommodation draft retrieved successfully']);
    });

    it('returns the correct draft id', function () {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson(route('api.accommodation.drafts.accommodation-draft.get'))
            ->assertSuccessful();

        expect($response->json('data.id'))->toBe($this->draft->id);
    });

    it('returns 404 when the user has no draft', function () {
        $userWithoutDraft = authenticatedUser();

        $this->actingAs($userWithoutDraft, 'sanctum')
            ->getJson(route('api.accommodation.drafts.accommodation-draft.get'))
            ->assertNotFound();
    });
});

// ============================================================
// POST /api/accommodation-drafts (createDraft)
// ============================================================

describe('POST /api/accommodation-drafts (createDraft)', function () {

    it('returns 401 for unauthenticated requests', function () {
        Auth::logout();
        $this->postJson(route('api.accommodation.drafts.accommodation-draft.create'), ['data' => []])
            ->assertUnauthorized();
    });

    it('returns 201 on success', function () {
        // Use a fresh user who has no existing draft
        $freshUser = authenticatedUser();

        $this->actingAs($freshUser, 'sanctum')
            ->postJson(route('api.accommodation.drafts.accommodation-draft.create'), ['data' => ['step' => 1]])
            ->assertCreated()
            ->assertJson(['success' => true, 'message' => 'Accommodation draft created successfully']);
    });

    it('returns 422 when data field is missing', function () {
        $freshUser = authenticatedUser();

        $this->actingAs($freshUser, 'sanctum')
            ->postJson(route('api.accommodation.drafts.accommodation-draft.create'), [])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'data']);
    });

    it('returns 422 when user already has an active draft', function () {
        // $this->user already has a draft from beforeEach
        $this->actingAs($this->user, 'sanctum')
            ->postJson(route('api.accommodation.drafts.accommodation-draft.create'), ['data' => ['step' => 1]])
            ->assertUnprocessable();
    });
});

// ============================================================
// PUT /api/accommodation-drafts/{accommodationDraft} (updateDraft)
// ============================================================

describe('PUT /api/accommodation-drafts/{id} (updateDraft)', function () {

    it('returns 401 for unauthenticated requests', function () {
        Auth::logout();
        $this->putJson(route('api.accommodation.drafts.accommodation-draft.update', $this->draft), [])
            ->assertUnauthorized();
    });

    it('returns 200 on success', function () {
        $this->actingAs($this->user, 'sanctum')
            ->putJson(route('api.accommodation.drafts.accommodation-draft.update', $this->draft), [
                'status' => 'draft',
                'current_step' => 3,
                'data' => ['title' => 'Updated Title'],
            ])
            ->assertSuccessful()
            ->assertJson(['success' => true, 'message' => 'Accommodation draft saved successfully']);
    });

    it('returns 422 when status is missing', function () {
        $this->actingAs($this->user, 'sanctum')
            ->putJson(route('api.accommodation.drafts.accommodation-draft.update', $this->draft), [
                'current_step' => 3,
                'data' => [],
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'status']);
    });

    it('returns 422 when current_step is missing', function () {
        $this->actingAs($this->user, 'sanctum')
            ->putJson(route('api.accommodation.drafts.accommodation-draft.update', $this->draft), [
                'status' => 'draft',
                'data' => [],
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'current_step']);
    });

    it('returns 422 when data is missing', function () {
        $this->actingAs($this->user, 'sanctum')
            ->putJson(route('api.accommodation.drafts.accommodation-draft.update', $this->draft), [
                'status' => 'draft',
                'current_step' => 3,
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'data']);
    });

    it('returns 422 for an invalid status value', function () {
        $this->actingAs($this->user, 'sanctum')
            ->putJson(route('api.accommodation.drafts.accommodation-draft.update', $this->draft), [
                'status' => 'published',
                'current_step' => 3,
                'data' => [],
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'status']);
    });

    it('accepts partial data in draft mode', function () {
        $this->actingAs($this->user, 'sanctum')
            ->putJson(route('api.accommodation.drafts.accommodation-draft.update', $this->draft), [
                'status' => 'draft',
                'current_step' => 2,
                'data' => [
                    'accommodation_type' => 'apartment',
                ],
            ])
            ->assertSuccessful();
    });

    it('rejects an invalid accommodation_type', function () {
        $this->actingAs($this->user, 'sanctum')
            ->putJson(route('api.accommodation.drafts.accommodation-draft.update', $this->draft), [
                'status' => 'draft',
                'current_step' => 2,
                'data' => [
                    'accommodation_type' => 'igloo_cave_hybrid',
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'data.accommodation_type']);
    });

    it('rejects an invalid accommodation_occupation', function () {
        $this->actingAs($this->user, 'sanctum')
            ->putJson(route('api.accommodation.drafts.accommodation-draft.update', $this->draft), [
                'status' => 'draft',
                'current_step' => 3,
                'data' => [
                    'accommodation_occupation' => 'penthouse_suite',
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'data.accommodation_occupation']);
    });

    it('rejects coordinates outside valid range', function () {
        $this->actingAs($this->user, 'sanctum')
            ->putJson(route('api.accommodation.drafts.accommodation-draft.update', $this->draft), [
                'status' => 'draft',
                'current_step' => 4,
                'data' => [
                    'coordinates' => ['latitude' => 999, 'longitude' => 20.4],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'data.coordinates.latitude']);
    });

    it('rejects an invalid country code', function () {
        $this->actingAs($this->user, 'sanctum')
            ->putJson(route('api.accommodation.drafts.accommodation-draft.update', $this->draft), [
                'status' => 'draft',
                'current_step' => 4,
                'data' => [
                    'address' => [
                        'country' => 'XX',
                        'street' => '123 Main St',
                        'city' => 'Belgrade',
                    ],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'data.address.country']);
    });

    it('rejects an invalid bed_type value', function () {
        $this->actingAs($this->user, 'sanctum')
            ->putJson(route('api.accommodation.drafts.accommodation-draft.update', $this->draft), [
                'status' => 'draft',
                'current_step' => 5,
                'data' => [
                    'floor_plan' => [
                        'guests' => 2,
                        'bedrooms' => 1,
                        'bathrooms' => 1,
                        'bed_types' => [
                            ['bed_type' => 'waterbed', 'quantity' => 1],
                        ],
                    ],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'data.floor_plan.bed_types.0.bed_type']);
    });

    it('rejects a basePrice below the minimum', function () {
        $this->actingAs($this->user, 'sanctum')
            ->putJson(route('api.accommodation.drafts.accommodation-draft.update', $this->draft), [
                'status' => 'draft',
                'current_step' => 10,
                'data' => [
                    'pricing' => [
                        'basePrice' => 5,
                        'bookingType' => 'instant_booking',
                        'minStay' => 1,
                    ],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'data.pricing.basePrice']);
    });

    it('rejects house_rules with an invalid time format', function () {
        $this->actingAs($this->user, 'sanctum')
            ->putJson(route('api.accommodation.drafts.accommodation-draft.update', $this->draft), [
                'status' => 'draft',
                'current_step' => 11,
                'data' => [
                    'house_rules' => [
                        'checkInFrom' => '25:00',
                        'checkInUntil' => '20:00',
                        'checkOutUntil' => '11:00',
                        'hasQuietHours' => false,
                        'cancellationPolicy' => 'moderate',
                    ],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'data.house_rules.checkInFrom']);
    });

    it('rejects an invalid cancellation policy', function () {
        $this->actingAs($this->user, 'sanctum')
            ->putJson(route('api.accommodation.drafts.accommodation-draft.update', $this->draft), [
                'status' => 'draft',
                'current_step' => 11,
                'data' => [
                    'house_rules' => [
                        'checkInFrom' => '15:00',
                        'checkInUntil' => '20:00',
                        'checkOutUntil' => '11:00',
                        'hasQuietHours' => false,
                        'cancellationPolicy' => 'super_strict',
                    ],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'data.house_rules.cancellationPolicy']);
    });

    it('requires all data fields when submitting for approval', function () {
        $this->actingAs($this->user, 'sanctum')
            ->putJson(route('api.accommodation.drafts.accommodation-draft.update', $this->draft), [
                'status' => 'waiting_for_approval',
                'current_step' => 12,
                'data' => [],
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'data.accommodation_type']);
    });

    it('accepts a complete and valid data payload on submission', function () {
        $this->actingAs($this->user, 'sanctum')
            ->putJson(route('api.accommodation.drafts.accommodation-draft.update', $this->draft), [
                'status' => 'waiting_for_approval',
                'current_step' => 12,
                'data' => [
                    'accommodation_type' => 'apartment',
                    'accommodation_occupation' => 'entire_place',
                    'address' => [
                        'country' => 'RS',
                        'street' => '123 Main St',
                        'city' => 'Belgrade',
                        'state' => null,
                        'zip_code' => '11000',
                    ],
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
                    'title' => 'Cozy Apartment in Belgrade',
                    'description' => 'A beautiful and cozy apartment located in the heart of Belgrade, perfect for any traveler.',
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
                        'quietHoursFrom' => '22:00',
                        'quietHoursUntil' => '08:00',
                        'cancellationPolicy' => 'moderate',
                    ],
                ],
            ])
            ->assertSuccessful();
    });
});

// ============================================================
// GET /api/accommodation-drafts/stats (getDraftStats)
// ============================================================

describe('GET /api/accommodation-drafts/stats (getDraftStats)', function () {

    it('returns 401 for unauthenticated requests', function () {
        Auth::logout();
        $this->getJson(route('api.accommodation.drafts.accommodation-draft.stats'))
            ->assertUnauthorized();
    });

    it('returns 200 with stats structure', function () {
        $this->actingAs($this->user, 'sanctum')
            ->getJson(route('api.accommodation.drafts.accommodation-draft.stats'))
            ->assertSuccessful()
            ->assertJsonStructure(['status', 'message', 'data', 'meta' => ['total_drafts']]);
    });

    it('returns status = success (not success boolean)', function () {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson(route('api.accommodation.drafts.accommodation-draft.stats'))
            ->assertSuccessful();

        expect($response->json('status'))->toBe('success');
    });

    it('reflects the correct total_drafts count', function () {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson(route('api.accommodation.drafts.accommodation-draft.stats'))
            ->assertSuccessful();

        // beforeEach created 1 draft-status draft
        expect($response->json('meta.total_drafts'))->toBe(1);
    });
});

// ============================================================
// POST /api/accommodation-drafts/{id}/photos (storePhotos)
// ============================================================

describe('POST /api/accommodation-drafts/{id}/photos (storePhotos)', function () {

    it('returns 401 for unauthenticated requests', function () {
        Auth::logout();
        $this->postJson(route('api.accommodation.drafts.accommodation-draft.photos.store', $this->draft), [])
            ->assertUnauthorized();
    });

    it('returns 422 when no photos are provided', function () {
        $this->actingAs($this->user, 'sanctum')
            ->postJson(route('api.accommodation.drafts.accommodation-draft.photos.store', $this->draft), [])
            ->assertUnprocessable();
    });

    it('queues photos and returns 202 with pending photo records', function () {
        Bus::fake();
        Storage::fake('local');

        $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson(route('api.accommodation.drafts.accommodation-draft.photos.store', $this->draft), [
                'photos' => [$file],
            ])
            ->assertStatus(202)
            ->assertJson(['success' => true, 'message' => 'Photos queued for processing']);

        expect($response->json('meta.queued_count'))->toBe(1);

        Bus::assertBatched(fn ($batch) => $batch->jobs->count() === 1);
        $this->assertDatabaseHas('photos', [
            'photoable_type' => AccommodationDraft::class,
            'photoable_id' => $this->draft->id,
            'status' => 'pending',
        ]);
    });

    it('marks the first uploaded photo as primary when none exists', function () {
        Bus::fake();
        Storage::fake('local');

        $file = UploadedFile::fake()->image('primary.jpg', 800, 600);

        $this->actingAs($this->user, 'sanctum')
            ->postJson(route('api.accommodation.drafts.accommodation-draft.photos.store', $this->draft), [
                'photos' => [$file],
            ])
            ->assertStatus(202);

        $this->assertDatabaseHas('photos', [
            'photoable_id' => $this->draft->id,
            'is_primary' => true,
        ]);
    });

    it('does not mark additional photos as primary when one already exists', function () {
        Bus::fake();
        Storage::fake('local');

        Photo::factory()->primary()->create([
            'photoable_type' => AccommodationDraft::class,
            'photoable_id' => $this->draft->id,
        ]);

        $file = UploadedFile::fake()->image('extra.jpg', 800, 600);

        $this->actingAs($this->user, 'sanctum')
            ->postJson(route('api.accommodation.drafts.accommodation-draft.photos.store', $this->draft), [
                'photos' => [$file],
            ])
            ->assertStatus(202);

        $primaryCount = Photo::where('photoable_id', $this->draft->id)
            ->where('is_primary', true)
            ->count();

        expect($primaryCount)->toBe(1);
    });
});

// ============================================================
// GET /api/accommodation-drafts/{id}/photos (getPhotos)
// ============================================================

describe('GET /api/accommodation-drafts/{id}/photos (getPhotos)', function () {

    it('returns 401 for unauthenticated requests', function () {
        Auth::logout();
        $this->getJson(route('api.accommodation.drafts.accommodation-draft.photos.index', $this->draft))
            ->assertUnauthorized();
    });

    it('returns 200 with an empty data array when the draft has no photos', function () {
        $this->actingAs($this->user, 'sanctum')
            ->getJson(route('api.accommodation.drafts.accommodation-draft.photos.index', $this->draft))
            ->assertSuccessful()
            ->assertJson(['success' => true])
            ->assertJsonCount(0, 'data');
    });

    it('returns photos belonging to the draft', function () {
        Photo::factory()->count(2)->create([
            'photoable_type' => AccommodationDraft::class,
            'photoable_id' => $this->draft->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson(route('api.accommodation.drafts.accommodation-draft.photos.index', $this->draft))
            ->assertSuccessful();

        expect(count($response->json('data')))->toBe(2);
    });
});

// ============================================================
// PUT /api/accommodation-drafts/{id}/photos/reorder (reorderPhotos)
// ============================================================

describe('PUT /api/accommodation-drafts/{id}/photos/reorder (reorderPhotos)', function () {

    it('returns 401 for unauthenticated requests', function () {
        Auth::logout();
        $this->putJson(route('api.accommodation.drafts.accommodation-draft.photos.reorder', $this->draft), [])
            ->assertUnauthorized();
    });

    it('returns 200 after reordering photos', function () {
        $photo1 = Photo::factory()->order(1)->create([
            'photoable_type' => AccommodationDraft::class,
            'photoable_id' => $this->draft->id,
        ]);
        $photo2 = Photo::factory()->order(2)->create([
            'photoable_type' => AccommodationDraft::class,
            'photoable_id' => $this->draft->id,
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->putJson(route('api.accommodation.drafts.accommodation-draft.photos.reorder', $this->draft), [
                'photo_ids' => [$photo2->id, $photo1->id],
            ])
            ->assertSuccessful()
            ->assertJson(['success' => true]);
    });

    it('returns 422 when photo_ids is missing', function () {
        $this->actingAs($this->user, 'sanctum')
            ->putJson(route('api.accommodation.drafts.accommodation-draft.photos.reorder', $this->draft), [])
            ->assertUnprocessable();
    });

    it('returns 422 when photo_ids contains a photo from another draft', function () {
        $otherDraft = AccommodationDraft::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'waiting_for_approval',
        ]);

        $foreignPhoto = Photo::factory()->create([
            'photoable_type' => AccommodationDraft::class,
            'photoable_id' => $otherDraft->id,
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->putJson(route('api.accommodation.drafts.accommodation-draft.photos.reorder', $this->draft), [
                'photo_ids' => [$foreignPhoto->id],
            ])
            ->assertUnprocessable();
    });
});

// ============================================================
// DELETE /api/accommodation-drafts/{id}/photos/{photo} (destroyPhoto)
// ============================================================

describe('DELETE /api/accommodation-drafts/{id}/photos/{photo} (destroyPhoto)', function () {

    it('returns 401 for unauthenticated requests', function () {
        Auth::logout();
        $photo = Photo::factory()->create([
            'photoable_type' => AccommodationDraft::class,
            'photoable_id' => $this->draft->id,
        ]);

        $url = "/api/accommodation-drafts/{$this->draft->id}/photos/{$photo->id}";

        $this->deleteJson($url)->assertUnauthorized();
    });

    it('returns 200 when the photo belongs to the draft', function () {
        $photo = Photo::factory()->create([
            'photoable_type' => AccommodationDraft::class,
            'photoable_id' => $this->draft->id,
        ]);

        $url = "/api/accommodation-drafts/{$this->draft->id}/photos/{$photo->id}";

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson($url)
            ->assertSuccessful()
            ->assertJson(['success' => true, 'message' => 'Photo deleted successfully.']);
    });

    it('returns 404 when the photo belongs to a different draft', function () {
        $otherDraft = AccommodationDraft::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'waiting_for_approval',
        ]);

        $photo = Photo::factory()->create([
            'photoable_type' => AccommodationDraft::class,
            'photoable_id' => $otherDraft->id,
        ]);

        // Try to delete via a different draft's URL
        $url = "/api/accommodation-drafts/{$this->draft->id}/photos/{$photo->id}";

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson($url)
            ->assertNotFound();
    });
});
