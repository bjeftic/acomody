<?php

use App\Models\AccommodationDraft;
use App\Models\HostProfile;
use App\Services\AccommodationService;
use Illuminate\Support\Facades\Mail;

// ============================================================
// GET /api/host/profile
// ============================================================

describe('GET /api/host/profile', function () {

    it('returns 401 for unauthenticated requests', function () {
        $this->getJson(route('api.host.profile.show'))
            ->assertUnauthorized();
    });

    it('returns null data when no host profile exists', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.host.profile.show'))
            ->assertSuccessful()
            ->assertJsonPath('data', null);
    });

    it('returns host profile data when it exists', function () {
        $user = authenticatedUser();
        HostProfile::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.host.profile.show'))
            ->assertSuccessful()
            ->assertJsonStructure(['data' => ['id', 'display_name', 'contact_email', 'phone', 'is_complete']]);
    });

});

// ============================================================
// POST /api/host/profile/initialize
// ============================================================

describe('POST /api/host/profile/initialize', function () {

    it('returns 401 for unauthenticated requests', function () {
        $this->postJson(route('api.host.profile.initialize'))
            ->assertUnauthorized();
    });

    it('creates an empty host profile when none exists', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.host.profile.initialize'))
            ->assertSuccessful();

        expect(HostProfile::where('user_id', $user->id)->exists())->toBeTrue();
    });

    it('returns existing host profile without creating a duplicate', function () {
        $user = authenticatedUser();
        HostProfile::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.host.profile.initialize'))
            ->assertSuccessful();

        expect(HostProfile::where('user_id', $user->id)->count())->toBe(1);
    });

    it('marks user as host after initialization', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.host.profile.initialize'));

        expect($user->fresh()->isHost())->toBeTrue();
    });

});

// ============================================================
// POST /api/host/profile
// ============================================================

describe('POST /api/host/profile', function () {

    it('returns 401 for unauthenticated requests', function () {
        $this->postJson(route('api.host.profile.store'), [])
            ->assertUnauthorized();
    });

    it('creates a host profile with valid data', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.host.profile.store'), [
                'display_name' => 'John Host',
                'contact_email' => 'john@example.com',
                'phone' => '+381600000000',
            ])
            ->assertSuccessful()
            ->assertJsonPath('data.display_name', 'John Host')
            ->assertJsonPath('data.is_complete', true);
    });

    it('returns 409 when host profile already exists', function () {
        $user = authenticatedUser();
        HostProfile::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.host.profile.store'), [
                'display_name' => 'John Host',
                'contact_email' => 'john@example.com',
                'phone' => '+381600000000',
            ])
            ->assertStatus(409);
    });

    it('validates required fields', function (string $field) {
        $user = authenticatedUser();

        $data = [
            'display_name' => 'John Host',
            'contact_email' => 'john@example.com',
            'phone' => '+381600000000',
        ];

        unset($data[$field]);

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.host.profile.store'), $data)
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => $field]);
    })->with(['display_name', 'contact_email', 'phone']);

});

// ============================================================
// PUT /api/host/profile
// ============================================================

describe('PUT /api/host/profile', function () {

    it('returns 401 for unauthenticated requests', function () {
        $this->putJson(route('api.host.profile.update'), [])
            ->assertUnauthorized();
    });

    it('returns 404 when no host profile exists', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.host.profile.update'), [
                'display_name' => 'Updated Name',
            ])
            ->assertNotFound();
    });

    it('updates host profile successfully', function () {
        $user = authenticatedUser();
        HostProfile::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.host.profile.update'), [
                'display_name' => 'Updated Name',
                'contact_email' => 'updated@example.com',
                'phone' => '+381611111111',
                'bio' => 'New bio',
            ])
            ->assertSuccessful()
            ->assertJsonPath('data.display_name', 'Updated Name')
            ->assertJsonPath('data.bio', 'New bio');
    });

    it('marks profile complete when all required fields are provided', function () {
        $user = authenticatedUser();
        HostProfile::factory()->incomplete()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.host.profile.update'), [
                'display_name' => 'John Host',
                'contact_email' => 'john@example.com',
                'phone' => '+381600000000',
            ])
            ->assertSuccessful()
            ->assertJsonPath('data.is_complete', true);
    });

    it('profile remains incomplete when only some required fields are filled', function () {
        $user = authenticatedUser();
        HostProfile::factory()->incomplete()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.host.profile.update'), [
                'display_name' => 'John Host',
                'bio' => 'Some bio',
            ])
            ->assertSuccessful()
            ->assertJsonPath('data.is_complete', false);
    });

});

// ============================================================
// User model host profile helpers
// ============================================================

describe('User host profile helpers', function () {

    it('isHost returns false when no host profile exists', function () {
        $user = authenticatedUser();
        expect($user->isHost())->toBeFalse();
    });

    it('isHost returns true when host profile exists', function () {
        $user = authenticatedUser();
        HostProfile::factory()->create(['user_id' => $user->id]);

        expect($user->fresh()->isHost())->toBeTrue();
    });

    it('hasCompleteHostProfile returns false when profile is incomplete', function () {
        $user = authenticatedUser();
        HostProfile::factory()->incomplete()->create(['user_id' => $user->id]);

        expect($user->fresh()->hasCompleteHostProfile())->toBeFalse();
    });

    it('hasCompleteHostProfile returns true when profile is complete', function () {
        $user = authenticatedUser();
        HostProfile::factory()->create(['user_id' => $user->id]);

        expect($user->fresh()->hasCompleteHostProfile())->toBeTrue();
    });

});

// ============================================================
// Draft submitted email notifications
// ============================================================

describe('draft submitted email notifications', function () {

    it('queues DraftSubmittedMail when host profile is complete', function () {
        Mail::fake();

        $user = authenticatedUser();
        HostProfile::factory()->create(['user_id' => $user->id]);

        $draft = AccommodationDraft::create([
            'user_id' => $user->id,
            'data' => json_encode(['title' => 'Test']),
            'current_step' => 2,
            'status' => 'draft',
            'last_saved_at' => now(),
        ]);

        AccommodationService::updateAccommodationDraft($draft, ['title' => 'Test'], 2, 'waiting_for_approval');

        Mail::assertQueued(\App\Mail\Accommodation\DraftSubmittedMail::class);
        Mail::assertNotQueued(\App\Mail\Accommodation\DraftSubmittedProfileIncompleteMail::class);
    });

    it('queues DraftSubmittedProfileIncompleteMail when host profile is incomplete', function () {
        Mail::fake();

        $user = authenticatedUser();
        HostProfile::factory()->incomplete()->create(['user_id' => $user->id]);

        $draft = AccommodationDraft::create([
            'user_id' => $user->id,
            'data' => json_encode(['title' => 'Test']),
            'current_step' => 2,
            'status' => 'draft',
            'last_saved_at' => now(),
        ]);

        AccommodationService::updateAccommodationDraft($draft, ['title' => 'Test'], 2, 'waiting_for_approval');

        Mail::assertQueued(\App\Mail\Accommodation\DraftSubmittedProfileIncompleteMail::class);
        Mail::assertNotQueued(\App\Mail\Accommodation\DraftSubmittedMail::class);
    });

    it('does not queue email when status does not transition to waiting_for_approval', function () {
        Mail::fake();

        $user = authenticatedUser();
        HostProfile::factory()->create(['user_id' => $user->id]);

        $draft = AccommodationDraft::create([
            'user_id' => $user->id,
            'data' => json_encode(['title' => 'Test']),
            'current_step' => 2,
            'status' => 'draft',
            'last_saved_at' => now(),
        ]);

        AccommodationService::updateAccommodationDraft($draft, ['title' => 'Test'], 3, 'draft');

        Mail::assertNothingQueued();
    });

});
