<?php

use App\Mail\SuperAdmin\SuperAdminInvitationMail;
use App\Mail\SuperAdmin\SuperAdminPasswordResetMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

// ============================================================
// GET /admin/superadmin-users (index)
// ============================================================

describe('GET /admin/superadmin-users', function () {
    it('is accessible to superadmins', function () {
        superadmin();

        $this->get('/admin/superadmin-users')->assertSuccessful();
    });

    it('redirects guests to login', function () {
        $this->get('/admin/superadmin-users')->assertRedirect();
    });

    it('redirects regular users away', function () {
        authenticatedUser();

        $this->get('/admin/superadmin-users')->assertRedirect();
    });

    it('only lists superadmin users', function () {
        superadmin();
        User::factory()->create(['is_superadmin' => true]);
        User::factory()->create(['is_superadmin' => false]);

        $response = $this->get('/admin/superadmin-users')->assertSuccessful();

        // 2 = the logged-in superadmin + the extra superadmin
        expect($response->original->getData()['superadmins']->total())->toBe(2);
    });

    it('filters by email search', function () {
        superadmin();
        User::factory()->create(['email' => 'findme@example.com', 'is_superadmin' => true]);
        User::factory()->create(['email' => 'other@example.com', 'is_superadmin' => true]);

        $response = $this->get('/admin/superadmin-users?search=findme')->assertSuccessful();

        expect($response->original->getData()['superadmins']->total())->toBe(1);
    });
});

// ============================================================
// GET /admin/superadmin-users/create
// ============================================================

describe('GET /admin/superadmin-users/create', function () {
    it('shows the create form to superadmins', function () {
        superadmin();

        $this->get('/admin/superadmin-users/create')->assertSuccessful();
    });

    it('redirects regular users away', function () {
        authenticatedUser();

        $this->get('/admin/superadmin-users/create')->assertRedirect();
    });
});

// ============================================================
// POST /admin/superadmin-users (store)
// ============================================================

describe('POST /admin/superadmin-users', function () {
    it('creates a new superadmin user and sends an invitation email', function () {
        Mail::fake();
        superadmin();

        $this->post('/admin/superadmin-users', [
            'email' => 'newadmin@example.com',
        ])->assertRedirect('/admin/superadmin-users');

        $this->assertDatabaseHas('users', [
            'email' => 'newadmin@example.com',
            'is_superadmin' => true,
        ]);

        Mail::assertQueued(SuperAdminInvitationMail::class, function ($mail) {
            return $mail->hasTo('newadmin@example.com');
        });
    });

    it('invitation email contains a set-password URL', function () {
        Mail::fake();
        superadmin();

        $this->post('/admin/superadmin-users', ['email' => 'newadmin@example.com']);

        Mail::assertQueued(SuperAdminInvitationMail::class, function ($mail) {
            return str_contains($mail->setPasswordUrl, '/admin/set-password');
        });
    });

    it('rejects a duplicate email', function () {
        superadmin();
        User::factory()->create(['email' => 'existing@example.com']);

        $this->post('/admin/superadmin-users', [
            'email' => 'existing@example.com',
        ])->assertSessionHasErrors('email');
    });

    it('requires email to be present', function () {
        superadmin();

        $this->post('/admin/superadmin-users', [])->assertSessionHasErrors('email');
    });

    it('redirects regular users away', function () {
        authenticatedUser();

        $this->post('/admin/superadmin-users', [
            'email' => 'newadmin@example.com',
        ])->assertRedirect();

        $this->assertDatabaseMissing('users', ['email' => 'newadmin@example.com']);
    });
});

// ============================================================
// GET /admin/superadmin-users/{id}/edit
// ============================================================

describe('GET /admin/superadmin-users/{id}/edit', function () {
    it('shows the edit form for a superadmin', function () {
        $admin = superadmin();

        $this->get("/admin/superadmin-users/{$admin->id}/edit")->assertSuccessful();
    });

    it('returns 404 for a regular user id', function () {
        superadmin();
        $regularUser = User::factory()->create(['is_superadmin' => false]);

        $this->get("/admin/superadmin-users/{$regularUser->id}/edit")->assertNotFound();
    });
});

// ============================================================
// PUT /admin/superadmin-users/{id} (update)
// ============================================================

describe('PUT /admin/superadmin-users/{id}', function () {
    it('updates the email of a superadmin', function () {
        superadmin();
        $target = User::factory()->create(['is_superadmin' => true]);

        $this->put("/admin/superadmin-users/{$target->id}", [
            'email' => 'updated@example.com',
        ])->assertRedirect('/admin/superadmin-users');

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'email' => 'updated@example.com',
        ]);
    });

    it('rejects a duplicate email on update', function () {
        superadmin();
        $target = User::factory()->create(['email' => 'target@example.com', 'is_superadmin' => true]);
        User::factory()->create(['email' => 'taken@example.com']);

        $this->put("/admin/superadmin-users/{$target->id}", [
            'email' => 'taken@example.com',
        ])->assertSessionHasErrors('email');
    });

    it('allows keeping the same email on update', function () {
        superadmin();
        $target = User::factory()->create(['email' => 'same@example.com', 'is_superadmin' => true]);

        $this->put("/admin/superadmin-users/{$target->id}", [
            'email' => 'same@example.com',
        ])->assertRedirect('/admin/superadmin-users');
    });
});

// ============================================================
// POST /admin/superadmin-users/{id}/reset-password
// ============================================================

describe('POST /admin/superadmin-users/{id}/reset-password', function () {
    it('sends a password reset email to the superadmin', function () {
        Mail::fake();
        superadmin();
        $target = User::factory()->create(['is_superadmin' => true]);

        $this->post("/admin/superadmin-users/{$target->id}/reset-password")
            ->assertRedirect('/admin/superadmin-users')
            ->assertSessionHas('alert-success');

        Mail::assertQueued(SuperAdminPasswordResetMail::class, function ($mail) use ($target) {
            return $mail->hasTo($target->email);
        });
    });

    it('reset email contains a set-password URL', function () {
        Mail::fake();
        superadmin();
        $target = User::factory()->create(['is_superadmin' => true]);

        $this->post("/admin/superadmin-users/{$target->id}/reset-password");

        Mail::assertQueued(SuperAdminPasswordResetMail::class, function ($mail) {
            return str_contains($mail->resetUrl, '/admin/set-password');
        });
    });

    it('redirects regular users away', function () {
        $target = superadmin();
        authenticatedUser();

        $this->post("/admin/superadmin-users/{$target->id}/reset-password")->assertRedirect();
    });
});

// ============================================================
// GET /admin/set-password
// ============================================================

describe('GET /admin/set-password', function () {
    it('is accessible without authentication', function () {
        $this->get('/admin/set-password?token=abc&email=admin@example.com')->assertSuccessful();
    });

    it('pre-fills email from the query string', function () {
        $this->get('/admin/set-password?token=abc&email=admin@example.com')
            ->assertSuccessful()
            ->assertSee('admin@example.com');
    });
});

// ============================================================
// POST /admin/set-password
// ============================================================

describe('POST /admin/set-password', function () {
    it('sets a new password with a valid token', function () {
        $user = User::factory()->create(['is_superadmin' => true]);
        $token = Password::broker()->createToken($user);

        $this->post('/admin/set-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewSecure123!',
            'password_confirmation' => 'NewSecure123!',
        ])->assertSuccessful()
            ->assertSee('Password set successfully');

        expect(\Illuminate\Support\Facades\Hash::check('NewSecure123!', $user->fresh()->password))->toBeTrue();
    });

    it('rejects an invalid token', function () {
        $user = User::factory()->create(['is_superadmin' => true]);

        $this->post('/admin/set-password', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'NewSecure123!',
            'password_confirmation' => 'NewSecure123!',
        ])->assertSessionHasErrors('email');
    });

    it('requires password and confirmation to match', function () {
        $user = User::factory()->create(['is_superadmin' => true]);
        $token = Password::broker()->createToken($user);

        $this->post('/admin/set-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewSecure123!',
            'password_confirmation' => 'DifferentPass456!',
        ])->assertSessionHasErrors('password');
    });
});

// ============================================================
// DELETE /admin/superadmin-users/{id}
// ============================================================

describe('DELETE /admin/superadmin-users/{id}', function () {
    it('deletes another superadmin', function () {
        superadmin();
        $target = User::factory()->create(['is_superadmin' => true]);

        $this->delete("/admin/superadmin-users/{$target->id}")
            ->assertRedirect('/admin/superadmin-users');

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    });

    it('prevents deleting your own account', function () {
        $admin = superadmin();

        $this->delete("/admin/superadmin-users/{$admin->id}")
            ->assertRedirect('/admin/superadmin-users')
            ->assertSessionHas('alert-danger');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    });

    it('returns 404 when targeting a regular user', function () {
        superadmin();
        $regularUser = User::factory()->create(['is_superadmin' => false]);

        $this->delete("/admin/superadmin-users/{$regularUser->id}")->assertNotFound();
    });

    it('redirects regular users away', function () {
        $target = superadmin();
        authenticatedUser();

        $this->delete("/admin/superadmin-users/{$target->id}")->assertRedirect();
    });
});
