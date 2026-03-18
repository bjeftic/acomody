<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

// ============================================================
// GET /admin/accommodations (index)
// ============================================================

describe('GET /admin/accommodations', function () {
    it('is accessible to superadmins', function () {
        $user = authenticatedUser();
        createAccommodation($user);
        superadmin();

        $this->get('/admin/accommodations')->assertSuccessful();
    });

    it('redirects guests to login', function () {
        $this->get('/admin/accommodations')->assertRedirect();
    });

    it('redirects regular users away', function () {
        authenticatedUser();

        $this->get('/admin/accommodations')->assertRedirect();
    });

    it('lists accommodations with host and location', function () {
        $user = User::factory()->create();
        createAccommodation($user);
        superadmin();

        $response = $this->get('/admin/accommodations')->assertSuccessful();

        $accommodations = $response->original->getData()['accommodations'];
        expect($accommodations->total())->toBeGreaterThanOrEqual(1);
    });

    it('searches accommodations by title', function () {
        $user = User::factory()->create();
        createAccommodation($user, ['title' => 'Unique Searchable Title XYZ']);
        createAccommodation($user, ['title' => 'Another Place']);
        superadmin();

        $response = $this->get('/admin/accommodations?search=Unique+Searchable+Title')
            ->assertSuccessful();

        $accommodations = $response->original->getData()['accommodations'];
        expect($accommodations->total())->toBe(1);
    });
});

// ============================================================
// GET /admin/accommodations/{id} (show)
// ============================================================

describe('GET /admin/accommodations/{id}', function () {
    it('shows accommodation details to a superadmin', function () {
        $user = User::factory()->create();
        $accommodation = createAccommodation($user);
        superadmin();

        $this->get("/admin/accommodations/{$accommodation->id}")->assertSuccessful();
    });

    it('displays the accommodation title', function () {
        $user = User::factory()->create();
        $accommodation = createAccommodation($user, ['title' => 'My Test Villa']);
        superadmin();

        $this->get("/admin/accommodations/{$accommodation->id}")
            ->assertSuccessful()
            ->assertSee('My Test Villa');
    });

    it('returns 404 for a non-existent accommodation', function () {
        superadmin();

        $this->get('/admin/accommodations/non-existent-id')->assertNotFound();
    });

    it('redirects guests to login', function () {
        $user = User::factory()->create();
        $accommodation = createAccommodation($user);
        Auth::logout();

        $this->get("/admin/accommodations/{$accommodation->id}")->assertRedirect();
    });

    it('redirects regular users away', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);
        authenticatedUser(); // switch to regular user

        $this->get("/admin/accommodations/{$accommodation->id}")->assertRedirect();
    });
});
