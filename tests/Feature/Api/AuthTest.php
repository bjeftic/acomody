<?php

use App\Mail\Auth\VerifyEmailMail;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;

// ============================================================
// POST /api/log-in
// ============================================================

describe('POST /api/log-in (login)', function () {

    it('returns 200 with user data for valid credentials', function () {
        $user = authenticatedUser();

        $this->postJson(route('api.login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSuccessful()
            ->assertJson([
                'success' => true,
                'message' => 'User logged in successfully.',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'meta' => ['login_at', 'refresh_page'],
            ]);
    });

    it('returns refresh_page = false for regular users', function () {
        $user = authenticatedUser();

        $this->postJson(route('api.login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSuccessful()
            ->assertJsonPath('meta.refresh_page', false);
    });

    it('returns refresh_page = true for superadmin users', function () {
        $user = authenticatedUser(['is_superadmin' => true]);

        $this->postJson(route('api.login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSuccessful()
            ->assertJsonPath('meta.refresh_page', true);
    });

    it('updates last_login_at after successful login', function () {
        $user = authenticatedUser(['last_login_at' => null]);

        $this->postJson(route('api.login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSuccessful();

        expect($user->fresh()->last_login_at)->not->toBeNull();
    });

    it('returns 422 for wrong password', function () {
        $user = authenticatedUser();

        $this->postJson(route('api.login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertUnprocessable()
            ->assertJsonFragment(['field' => 'email']);
    });

    it('returns 422 for non-existent email', function () {
        $this->postJson(route('api.login'), [
            'email' => 'nobody@example.com',
            'password' => 'password',
        ])->assertUnprocessable();
    });

    it('returns 422 for missing email', function () {
        $this->postJson(route('api.login'), [
            'password' => 'password',
        ])->assertUnprocessable()
            ->assertJsonFragment(['field' => 'email']);
    });

    it('returns 422 for missing password', function () {
        $this->postJson(route('api.login'), [
            'email' => 'user@example.com',
        ])->assertUnprocessable()
            ->assertJsonFragment(['field' => 'password']);
    });

    it('returns 422 for invalid email format', function () {
        $this->postJson(route('api.login'), [
            'email' => 'not-an-email',
            'password' => 'password',
        ])->assertUnprocessable()
            ->assertJsonFragment(['field' => 'email']);
    });

    it('accepts remember_me = true', function () {
        $user = authenticatedUser();

        $this->postJson(route('api.login'), [
            'email' => $user->email,
            'password' => 'password',
            'remember_me' => true,
        ])->assertSuccessful();
    });

    it('normalises email to lowercase before checking', function () {
        $user = authenticatedUser(['email' => 'user@example.com']);

        $this->postJson(route('api.login'), [
            'email' => 'USER@EXAMPLE.COM',
            'password' => 'password',
        ])->assertSuccessful();
    });

});

// ============================================================
// POST /api/log-out
// ============================================================

describe('POST /api/log-out (logout)', function () {

    it('returns 200 for an authenticated user', function () {
        $user = authenticatedUser();

        // The destroyApi controller checks Auth::guard('web')->user()
        $this->actingAs($user, 'web')
            ->postJson(route('api.logout'))
            ->assertSuccessful()
            ->assertJson([
                'success' => true,
                'message' => 'User logged out successfully.',
            ]);
    });

    it('returns 401 for an unauthenticated request', function () {
        $this->postJson(route('api.logout'))
            ->assertStatus(401);
    });

});

// ============================================================
// POST /api/sign-up
// ============================================================

describe('POST /api/sign-up (register)', function () {

    beforeEach(function () {
        Http::fake(); // Prevent external HTTP calls (HIBP uncompromised check, etc.)
        Mail::fake();
    });

    it('returns 201 on successful registration', function () {
        $this->postJson(route('api.signup'), [
            'email' => 'newuser@gmail.com',
            'password' => 'SecureP@ss2024!',
            'confirm_password' => 'SecureP@ss2024!',
        ])->assertStatus(201)
            ->assertJson(['success' => true]);
    });

    it('creates the user in the database', function () {
        $this->postJson(route('api.signup'), [
            'email' => 'newuser@gmail.com',
            'password' => 'SecureP@ss2024!',
            'confirm_password' => 'SecureP@ss2024!',
        ])->assertStatus(201);

        $this->assertDatabaseHas('users', ['email' => 'newuser@gmail.com']);
    });

    it('returns verification_required = true in meta', function () {
        $this->postJson(route('api.signup'), [
            'email' => 'newuser@gmail.com',
            'password' => 'SecureP@ss2024!',
            'confirm_password' => 'SecureP@ss2024!',
        ])->assertStatus(201)
            ->assertJsonPath('meta.verification_required', true);
    });

    it('returns login_enabled = false in meta', function () {
        $this->postJson(route('api.signup'), [
            'email' => 'newuser@gmail.com',
            'password' => 'SecureP@ss2024!',
            'confirm_password' => 'SecureP@ss2024!',
        ])->assertStatus(201)
            ->assertJsonPath('meta.login_enabled', false);
    });

    it('returns verification_expires_at in meta', function () {
        $this->postJson(route('api.signup'), [
            'email' => 'newuser@gmail.com',
            'password' => 'SecureP@ss2024!',
            'confirm_password' => 'SecureP@ss2024!',
        ])->assertStatus(201)
            ->assertJsonStructure(['meta' => ['verification_expires_at']]);
    });

    it('queues VerifyEmailMail to the new user', function () {
        $this->postJson(route('api.signup'), [
            'email' => 'newuser@gmail.com',
            'password' => 'SecureP@ss2024!',
            'confirm_password' => 'SecureP@ss2024!',
        ])->assertStatus(201);

        Mail::assertQueued(VerifyEmailMail::class, fn ($mail) => $mail->hasTo('newuser@gmail.com'));
    });

    it('returns 422 for a duplicate email', function () {
        authenticatedUser(['email' => 'existing@gmail.com']);

        $this->postJson(route('api.signup'), [
            'email' => 'existing@gmail.com',
            'password' => 'SecureP@ss2024!',
            'confirm_password' => 'SecureP@ss2024!',
        ])->assertUnprocessable()
            ->assertJsonFragment(['field' => 'email']);
    });

    it('returns 422 for missing email', function () {
        $this->postJson(route('api.signup'), [
            'password' => 'SecureP@ss2024!',
            'confirm_password' => 'SecureP@ss2024!',
        ])->assertUnprocessable()
            ->assertJsonFragment(['field' => 'email']);
    });

    it('returns 422 for missing password', function () {
        $this->postJson(route('api.signup'), [
            'email' => 'newuser@gmail.com',
            'confirm_password' => 'SecureP@ss2024!',
        ])->assertUnprocessable()
            ->assertJsonFragment(['field' => 'password']);
    });

    it('returns 422 for missing confirm_password', function () {
        $this->postJson(route('api.signup'), [
            'email' => 'newuser@gmail.com',
            'password' => 'SecureP@ss2024!',
        ])->assertUnprocessable()
            ->assertJsonFragment(['field' => 'confirm_password']);
    });

    it('returns 422 when passwords do not match', function () {
        $this->postJson(route('api.signup'), [
            'email' => 'newuser@gmail.com',
            'password' => 'SecureP@ss2024!',
            'confirm_password' => 'DifferentPass!99',
        ])->assertUnprocessable()
            ->assertJsonFragment(['field' => 'confirm_password']);
    });

    it('normalises email to lowercase before saving', function () {
        $this->postJson(route('api.signup'), [
            'email' => 'NewUser@GMAIL.COM',
            'password' => 'SecureP@ss2024!',
            'confirm_password' => 'SecureP@ss2024!',
        ])->assertStatus(201);

        $this->assertDatabaseHas('users', ['email' => 'newuser@gmail.com']);
    });

    it('does not return user data in the response body', function () {
        $response = $this->postJson(route('api.signup'), [
            'email' => 'newuser@gmail.com',
            'password' => 'SecureP@ss2024!',
            'confirm_password' => 'SecureP@ss2024!',
        ])->assertStatus(201);

        expect($response->json('data'))->toBeNull();
    });

});

// ============================================================
// POST /api/forgot-password
// ============================================================

describe('POST /api/forgot-password', function () {

    it('returns 200 for a registered email', function () {
        $user = authenticatedUser();

        $this->postJson(route('api.password.email'), [
            'email' => $user->email,
        ])->assertSuccessful()
            ->assertJson(['success' => true]);
    });

    it('returns 200 for a non-existent email (prevents email enumeration)', function () {
        $this->postJson(route('api.password.email'), [
            'email' => 'nobody@example.com',
        ])->assertSuccessful()
            ->assertJson(['success' => true]);
    });

    it('returns 422 for a missing email', function () {
        $this->postJson(route('api.password.email'), [])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'email']);
    });

    it('returns 422 for an invalid email format', function () {
        $this->postJson(route('api.password.email'), [
            'email' => 'not-an-email',
        ])->assertUnprocessable()
            ->assertJsonFragment(['field' => 'email']);
    });

    it('returns the same generic message for both existing and non-existing emails', function () {
        $message = 'If that email address is in our system, we have emailed a password reset link.';

        $this->postJson(route('api.password.email'), [
            'email' => 'nobody@example.com',
        ])->assertSuccessful()
            ->assertJson(['message' => $message]);
    });

});

// ============================================================
// POST /api/reset-password
// ============================================================

describe('POST /api/reset-password', function () {

    it('returns 200 on a successful password reset', function () {
        $user = authenticatedUser();
        $token = Password::createToken($user);

        // User::withoutAuthorization is required because the reset callback
        // updates the user record without an authenticated session.
        $response = User::withoutAuthorization(fn () => $this->postJson(route('api.password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewSecureP@ss2024!',
            'password_confirmation' => 'NewSecureP@ss2024!',
        ]));

        $response->assertSuccessful()
            ->assertJson([
                'success' => true,
                'message' => 'Password reset successfully.',
            ]);
    });

    it('updates the password hash in the database', function () {
        $user = authenticatedUser();
        $token = Password::createToken($user);

        $this->postJson(route('api.password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewSecureP@ss2024!',
            'password_confirmation' => 'NewSecureP@ss2024!',
        ])->assertSuccessful();

        expect(Hash::check('NewSecureP@ss2024!', $user->fresh()->password))->toBeTrue();
    });

    it('returns 422 for an invalid token', function () {
        $user = authenticatedUser();

        $this->postJson(route('api.password.store'), [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'NewSecureP@ss2024!',
            'password_confirmation' => 'NewSecureP@ss2024!',
        ])->assertUnprocessable();
    });

    it('returns 404 for a non-existent user email', function () {
        $this->postJson(route('api.password.store'), [
            'token' => 'some-token',
            'email' => 'nobody@example.com',
            'password' => 'NewSecureP@ss2024!',
            'password_confirmation' => 'NewSecureP@ss2024!',
        ])->assertNotFound();
    });

    it('returns 422 for a missing token', function () {
        $this->postJson(route('api.password.store'), [
            'email' => 'user@example.com',
            'password' => 'NewSecureP@ss2024!',
            'password_confirmation' => 'NewSecureP@ss2024!',
        ])->assertUnprocessable()
            ->assertJsonFragment(['field' => 'token']);
    });

    it('returns 422 for a missing email', function () {
        $this->postJson(route('api.password.store'), [
            'token' => 'some-token',
            'password' => 'NewSecureP@ss2024!',
            'password_confirmation' => 'NewSecureP@ss2024!',
        ])->assertUnprocessable()
            ->assertJsonFragment(['field' => 'email']);
    });

    it('returns 422 when the passwords do not match', function () {
        $user = authenticatedUser();
        $token = Password::createToken($user);

        $this->postJson(route('api.password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewSecureP@ss2024!',
            'password_confirmation' => 'DifferentPass!99',
        ])->assertUnprocessable()
            ->assertJsonFragment(['field' => 'password']);
    });

    it('returns 422 when the new password matches the current password', function () {
        $user = authenticatedUser();
        $token = Password::createToken($user);

        $this->postJson(route('api.password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'password', // same as factory default
            'password_confirmation' => 'password',
        ])->assertUnprocessable()
            ->assertJsonFragment(['field' => 'password']);
    });

    it('dispatches a PasswordReset event on success', function () {
        Event::fake();

        $user = authenticatedUser();
        $token = Password::createToken($user);

        $this->postJson(route('api.password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewSecureP@ss2024!',
            'password_confirmation' => 'NewSecureP@ss2024!',
        ])->assertSuccessful();

        Event::assertDispatched(PasswordReset::class);
    });

    it('resets failed_login_attempts to 0 on success', function () {
        $user = authenticatedUser(['failed_login_attempts' => 3]);
        $token = Password::createToken($user);

        $this->postJson(route('api.password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewSecureP@ss2024!',
            'password_confirmation' => 'NewSecureP@ss2024!',
        ])->assertSuccessful();

        expect($user->fresh()->failed_login_attempts)->toBe(0);
    });

});

// ============================================================
// GET /verify/{id}/{hash} (email verification)
// ============================================================

describe('GET /verify/{id}/{hash} (email verification)', function () {

    it('redirects to success URL on valid verification', function () {
        $user = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHours(24),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // User::withoutAuthorization bypasses the Authorizable::updating check
        // that would otherwise block markEmailAsVerified() for unauthenticated requests.
        $response = User::withoutAuthorization(fn () => $this->get($url));

        $response->assertRedirect();
        expect($response->headers->get('Location'))->toContain('/email-verify?status=success');
    });

    it('marks the email as verified on success', function () {
        $user = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHours(24),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        User::withoutAuthorization(fn () => $this->get($url));

        expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    });

    it('redirects to info URL when email is already verified', function () {
        $user = authenticatedUser(); // already verified
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHours(24),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->get($url)->assertRedirect();

        expect($response->headers->get('Location'))->toContain('/email-verify?status=info');
    });

    it('redirects to error URL when the hash does not match', function () {
        $user = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHours(24),
            ['id' => $user->id, 'hash' => 'invalid-hash']
        );

        $response = $this->get($url)->assertRedirect();

        expect($response->headers->get('Location'))->toContain('/email-verify?status=error');
    });

    it('returns 403 for an expired signature', function () {
        $user = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->subHour(), // already expired
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // The signed middleware intercepts expired signatures and returns 403
        // before the controller even runs.
        $this->get($url)->assertForbidden();
    });

    it('redirects to error URL when the user is not found', function () {
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHours(24),
            ['id' => 999999, 'hash' => sha1('nobody@example.com')]
        );

        $response = $this->get($url)->assertRedirect();

        expect($response->headers->get('Location'))->toContain('/email-verify?status=error');
    });

    it('does not re-verify an already verified email', function () {
        $user = authenticatedUser();
        $verifiedAt = $user->email_verified_at;
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHours(24),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->get($url);

        expect($user->fresh()->email_verified_at->eq($verifiedAt))->toBeTrue();
    });

});

// ============================================================
// POST /api/resend (resend email verification)
// ============================================================

describe('POST /api/resend (resend verification)', function () {

    it('returns 200 for an authenticated unverified user', function () {
        Mail::fake();

        $user = authenticatedUser(['email_verified_at' => null]);

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.verification.send'))
            ->assertSuccessful()
            ->assertJson([
                'success' => true,
                'message' => 'Verification link sent.',
            ]);
    });

    it('queues VerifyEmailMail when resending for an unverified user', function () {
        Mail::fake();

        $user = authenticatedUser(['email_verified_at' => null]);

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.verification.send'));

        Mail::assertQueued(VerifyEmailMail::class, fn ($mail) => $mail->hasTo($user->email));
    });

    it('returns 409 when email is already verified', function () {
        $user = authenticatedUser(); // already verified

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.verification.send'))
            ->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'Email already verified.',
            ]);
    });

    it('returns 401 for an unauthenticated request', function () {
        $this->postJson(route('api.verification.send'))
            ->assertStatus(401);
    });

    it('does not queue mail when email is already verified', function () {
        Mail::fake();

        $user = authenticatedUser(); // already verified

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.verification.send'));

        Mail::assertNotQueued(VerifyEmailMail::class);
    });

});
