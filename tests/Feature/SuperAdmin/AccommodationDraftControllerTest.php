<?php

use App\Jobs\CreateAccommodation;
use App\Mail\Accommodation\AccommodationRejectedMail;
use App\Mail\Accommodation\ReviewCommentAddedMail;
use App\Models\AccommodationDraft;
use App\Models\ReviewComment;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;

// ============================================================
// Helpers
// ============================================================

function makeDraft(array $attributes = []): AccommodationDraft
{
    // Creates a regular user (draft owner) and logs them in.
    // Tests that need a superadmin session must call superadmin() AFTER this.
    $user = authenticatedUser();

    return AccommodationDraft::factory()->create(array_merge([
        'user_id' => $user->id,
        'status' => 'waiting_for_approval',
    ], $attributes));
}

// ============================================================
// GET /admin/accommodation-drafts (index)
// ============================================================

describe('GET /admin/accommodation-drafts', function () {
    it('is accessible to superadmins', function () {
        $draft = makeDraft();
        superadmin();

        $this->get('/admin/accommodation-drafts')->assertSuccessful();
    });

    it('redirects guests to login', function () {
        $this->get('/admin/accommodation-drafts')->assertRedirect();
    });

    it('redirects regular users away', function () {
        authenticatedUser();

        $this->get('/admin/accommodation-drafts')->assertRedirect();
    });

    it('only lists drafts with waiting_for_approval status', function () {
        makeDraft(['status' => 'waiting_for_approval']);
        makeDraft(['status' => 'draft']);
        makeDraft(['status' => 'published']);
        superadmin();

        $response = $this->get('/admin/accommodation-drafts')->assertSuccessful();

        expect($response->original->getData()['accommodationDrafts']->total())->toBe(1);
    });
});

// ============================================================
// GET /admin/accommodation-drafts/{id} (show) — including locking
// ============================================================

describe('GET /admin/accommodation-drafts/{id}', function () {
    it('shows draft details to a superadmin', function () {
        $draft = makeDraft();
        superadmin();

        $this->get("/admin/accommodation-drafts/{$draft->id}")->assertSuccessful();
    });

    it('acquires a lock when a superadmin opens the draft', function () {
        $draft = makeDraft();
        $admin = superadmin();

        $this->get("/admin/accommodation-drafts/{$draft->id}")->assertSuccessful();

        $this->assertDatabaseHas('accommodation_drafts', [
            'id' => $draft->id,
            'locked_by_id' => $admin->id,
        ]);
    });

    it('blocks a second superadmin if the draft is already locked', function () {
        $draft = makeDraft();
        $firstAdmin = superadmin();

        // First admin acquires the lock
        $draft->acquireLock($firstAdmin);

        // Second admin tries to open
        superadmin();

        $this->get("/admin/accommodation-drafts/{$draft->id}")
            ->assertRedirect('/admin/accommodation-drafts');
    });

    it('allows reopening an expired lock', function () {
        $draft = makeDraft();
        $firstAdmin = superadmin();

        // Simulate an expired lock by a different admin
        $draft->update([
            'locked_by_id' => $firstAdmin->id,
            'locked_at' => now()->subMinutes(31),
        ]);

        $secondAdmin = superadmin();

        $this->get("/admin/accommodation-drafts/{$draft->id}")->assertSuccessful();

        $this->assertDatabaseHas('accommodation_drafts', [
            'id' => $draft->id,
            'locked_by_id' => $secondAdmin->id,
        ]);
    });

    it('allows the same admin to re-open a draft they already locked', function () {
        $draft = makeDraft();
        $admin = superadmin();

        $draft->acquireLock($admin);

        $this->get("/admin/accommodation-drafts/{$draft->id}")->assertSuccessful();
    });

    it('redirects regular users away', function () {
        $draft = makeDraft();

        $this->get("/admin/accommodation-drafts/{$draft->id}")->assertRedirect();
    });

    it('shows review comments on the draft', function () {
        $draft = makeDraft();
        $admin = superadmin();

        ReviewComment::create([
            'commentable_id' => $draft->id,
            'commentable_type' => AccommodationDraft::class,
            'user_id' => $admin->id,
            'body' => 'Please update the photos.',
        ]);

        $this->get("/admin/accommodation-drafts/{$draft->id}")
            ->assertSuccessful()
            ->assertSee('Please update the photos.');
    });

    it('returns 404 for a non-existent draft', function () {
        superadmin();

        $this->get('/admin/accommodation-drafts/non-existent-id')->assertNotFound();
    });

    it('renders bed type labels on the show page', function () {
        $user = authenticatedUser();
        superadmin();

        $draft = AccommodationDraft::factory()->create([
            'user_id' => $user->id,
            'status' => 'waiting_for_approval',
            'data' => json_encode([
                'accommodation_type' => 'apartment',
                'accommodation_occupation' => 'entire_place',
                'title' => 'My Place',
                'description' => 'Nice place.',
                'address' => ['country' => 'RS', 'street' => '1 St', 'city' => 'BG', 'state' => null, 'zip_code' => '11000'],
                'coordinates' => ['latitude' => 44.8, 'longitude' => 20.4],
                'floor_plan' => [
                    'guests' => 2,
                    'bedrooms' => 1,
                    'bathrooms' => 1,
                    'bed_types' => [
                        ['bed_type' => 'double', 'quantity' => 1],
                        ['bed_type' => 'king', 'quantity' => 2],
                    ],
                ],
                'amenities' => [],
                'pricing' => ['basePrice' => 50, 'bookingType' => 'instant_booking'],
                'house_rules' => ['checkInFrom' => '15:00', 'checkInUntil' => '20:00', 'checkOutUntil' => '11:00', 'hasQuietHours' => false, 'quietHoursFrom' => '22:00', 'quietHoursUntil' => '08:00', 'cancellationPolicy' => 'moderate'],
            ]),
        ]);

        $this->get("/admin/accommodation-drafts/{$draft->id}")
            ->assertSuccessful()
            ->assertSee('Double Bed')
            ->assertSee('King Bed');
    });

    it('renders the postal code from zip_code in draft data', function () {
        $user = authenticatedUser();
        superadmin();

        $draft = AccommodationDraft::factory()->create([
            'user_id' => $user->id,
            'status' => 'waiting_for_approval',
            'data' => json_encode([
                'accommodation_type' => 'apartment',
                'accommodation_occupation' => 'entire_place',
                'title' => 'My Place',
                'description' => 'Nice place.',
                'address' => ['country' => 'RS', 'street' => '1 St', 'city' => 'BG', 'state' => null, 'zip_code' => '11000'],
                'coordinates' => ['latitude' => 44.8, 'longitude' => 20.4],
                'floor_plan' => ['guests' => 2, 'bedrooms' => 1, 'bathrooms' => 1, 'bed_types' => []],
                'amenities' => [],
                'pricing' => ['basePrice' => 50, 'bookingType' => 'instant_booking'],
                'house_rules' => ['checkInFrom' => '15:00', 'checkInUntil' => '20:00', 'checkOutUntil' => '11:00', 'hasQuietHours' => false, 'quietHoursFrom' => '22:00', 'quietHoursUntil' => '08:00', 'cancellationPolicy' => 'moderate'],
            ]),
        ]);

        $this->get("/admin/accommodation-drafts/{$draft->id}")
            ->assertSuccessful()
            ->assertSee('11000');
    });
});

// ============================================================
// POST /admin/accommodation-drafts/{id}/approve
// ============================================================

describe('POST /admin/accommodation-drafts/{id}/approve', function () {
    it('dispatches the CreateAccommodation job and sets status to processing', function () {
        Bus::fake();
        $draft = makeDraft();
        superadmin();
        $location = makeLocation();

        $this->post("/admin/accommodation-drafts/{$draft->id}/approve", [
            'location_id' => $location->id,
        ])->assertRedirect('/admin/accommodation-drafts');

        $this->assertDatabaseHas('accommodation_drafts', [
            'id' => $draft->id,
            'status' => 'processing',
        ]);

        Bus::assertDispatched(CreateAccommodation::class);
    });

    it('requires a valid location_id', function () {
        $draft = makeDraft();
        superadmin();

        $this->post("/admin/accommodation-drafts/{$draft->id}/approve", [
            'location_id' => 'non-existent',
        ])->assertSessionHasErrors('location_id');
    });

    it('requires location_id to be present', function () {
        $draft = makeDraft();
        superadmin();

        $this->post("/admin/accommodation-drafts/{$draft->id}/approve", [])
            ->assertSessionHasErrors('location_id');
    });

    it('redirects regular users away', function () {
        $location = makeLocation(); // creates as superadmin
        $draft = makeDraft();       // switches to regular user

        $this->post("/admin/accommodation-drafts/{$draft->id}/approve", [
            'location_id' => $location->id,
        ])->assertRedirect();
    });
});

// ============================================================
// POST /admin/accommodation-drafts/{id}/reject
// ============================================================

describe('POST /admin/accommodation-drafts/{id}/reject', function () {
    it('sets status to rejected', function () {
        Mail::fake();
        $draft = makeDraft();
        superadmin();

        $this->post("/admin/accommodation-drafts/{$draft->id}/reject")
            ->assertRedirect('/admin/accommodation-drafts');

        $this->assertDatabaseHas('accommodation_drafts', [
            'id' => $draft->id,
            'status' => 'rejected',
        ]);
    });

    it('sends a rejection email to the host', function () {
        Mail::fake();
        $draft = makeDraft();
        superadmin();

        $this->post("/admin/accommodation-drafts/{$draft->id}/reject");

        Mail::assertQueued(AccommodationRejectedMail::class, function ($mail) use ($draft) {
            return $mail->draft->id === $draft->id;
        });
    });

    it('creates a review comment when a reason is provided', function () {
        Mail::fake();
        $draft = makeDraft();
        superadmin();

        $this->post("/admin/accommodation-drafts/{$draft->id}/reject", [
            'reason' => 'Photos are too dark.',
        ]);

        $this->assertDatabaseHas('review_comments', [
            'commentable_id' => $draft->id,
            'body' => 'Photos are too dark.',
        ]);
    });

    it('does not create a comment when no reason is provided', function () {
        Mail::fake();
        $draft = makeDraft();
        superadmin();

        $this->post("/admin/accommodation-drafts/{$draft->id}/reject");

        expect(ReviewComment::count())->toBe(0);
    });

    it('includes the reason in the rejection email', function () {
        Mail::fake();
        $draft = makeDraft();
        superadmin();

        $this->post("/admin/accommodation-drafts/{$draft->id}/reject", [
            'reason' => 'Missing amenities.',
        ]);

        Mail::assertQueued(AccommodationRejectedMail::class, function ($mail) {
            return $mail->reason === 'Missing amenities.';
        });
    });

    it('rejects a reason that exceeds 2000 characters', function () {
        $draft = makeDraft();
        superadmin();

        $this->post("/admin/accommodation-drafts/{$draft->id}/reject", [
            'reason' => str_repeat('a', 2001),
        ])->assertSessionHasErrors('reason');
    });

    it('redirects regular users away', function () {
        $draft = makeDraft();

        $this->post("/admin/accommodation-drafts/{$draft->id}/reject")->assertRedirect();
    });
});

// ============================================================
// POST /admin/accommodation-drafts/{id}/comments
// ============================================================

describe('POST /admin/accommodation-drafts/{id}/comments', function () {
    it('creates a review comment and sends an email', function () {
        Mail::fake();
        $draft = makeDraft();
        $admin = superadmin();

        $this->post("/admin/accommodation-drafts/{$draft->id}/comments", [
            'body' => 'Please add more photos.',
        ])->assertRedirect("/admin/accommodation-drafts/{$draft->id}");

        $this->assertDatabaseHas('review_comments', [
            'commentable_id' => $draft->id,
            'user_id' => $admin->id,
            'body' => 'Please add more photos.',
        ]);

        Mail::assertQueued(ReviewCommentAddedMail::class, function ($mail) use ($draft) {
            return $mail->draft->id === $draft->id
                && $mail->comment->body === 'Please add more photos.';
        });
    });

    it('sends the email to the draft owner', function () {
        Mail::fake();
        $draft = makeDraft();
        $draftOwner = $draft->user;
        superadmin();

        $this->post("/admin/accommodation-drafts/{$draft->id}/comments", [
            'body' => 'Needs more detail.',
        ]);

        Mail::assertQueued(ReviewCommentAddedMail::class, function ($mail) use ($draftOwner) {
            return $mail->hasTo($draftOwner->email);
        });
    });

    it('requires body to be present', function () {
        $draft = makeDraft();
        superadmin();

        $this->post("/admin/accommodation-drafts/{$draft->id}/comments", [])
            ->assertSessionHasErrors('body');
    });

    it('rejects a body that exceeds 2000 characters', function () {
        $draft = makeDraft();
        superadmin();

        $this->post("/admin/accommodation-drafts/{$draft->id}/comments", [
            'body' => str_repeat('a', 2001),
        ])->assertSessionHasErrors('body');
    });

    it('allows multiple comments on the same draft', function () {
        Mail::fake();
        $draft = makeDraft();
        superadmin();

        $this->post("/admin/accommodation-drafts/{$draft->id}/comments", ['body' => 'First comment.']);
        $this->post("/admin/accommodation-drafts/{$draft->id}/comments", ['body' => 'Second comment.']);

        expect(ReviewComment::where('commentable_id', $draft->id)->count())->toBe(2);
    });

    it('redirects regular users away', function () {
        $draft = makeDraft();

        $this->post("/admin/accommodation-drafts/{$draft->id}/comments", [
            'body' => 'Comment.',
        ])->assertRedirect();
    });
});

// ============================================================
// POST /admin/accommodation-drafts/{id}/release-lock
// ============================================================

describe('POST /admin/accommodation-drafts/{id}/release-lock', function () {
    it('releases the lock when called by the lock holder', function () {
        $draft = makeDraft();
        $admin = superadmin();

        $draft->acquireLock($admin);

        $this->post("/admin/accommodation-drafts/{$draft->id}/release-lock")
            ->assertRedirect('/admin/accommodation-drafts');

        $this->assertDatabaseHas('accommodation_drafts', [
            'id' => $draft->id,
            'locked_by_id' => null,
        ]);
    });

    it('does not release the lock if called by a different admin', function () {
        $draft = makeDraft();
        $firstAdmin = superadmin();
        $draft->acquireLock($firstAdmin);

        // Second admin tries to release
        superadmin();

        $this->post("/admin/accommodation-drafts/{$draft->id}/release-lock")
            ->assertRedirect('/admin/accommodation-drafts');

        // Lock still held by first admin
        $this->assertDatabaseHas('accommodation_drafts', [
            'id' => $draft->id,
            'locked_by_id' => $firstAdmin->id,
        ]);
    });

    it('redirects regular users away', function () {
        $draft = makeDraft();

        $this->post("/admin/accommodation-drafts/{$draft->id}/release-lock")->assertRedirect();
    });
});

// ============================================================
// Lock released on approve / reject
// ============================================================

describe('Lock is cleared after approve or reject', function () {
    it('clears the lock when a draft is approved', function () {
        Bus::fake();
        $draft = makeDraft();
        $admin = superadmin();
        $location = makeLocation();

        $draft->acquireLock($admin);

        $this->post("/admin/accommodation-drafts/{$draft->id}/approve", [
            'location_id' => $location->id,
        ]);

        $this->assertDatabaseHas('accommodation_drafts', [
            'id' => $draft->id,
            'locked_by_id' => null,
        ]);
    });

    it('clears the lock when a draft is rejected', function () {
        Mail::fake();
        $draft = makeDraft();
        $admin = superadmin();

        $draft->acquireLock($admin);

        $this->post("/admin/accommodation-drafts/{$draft->id}/reject");

        $this->assertDatabaseHas('accommodation_drafts', [
            'id' => $draft->id,
            'locked_by_id' => null,
        ]);
    });
});
