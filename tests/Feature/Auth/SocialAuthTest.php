<?php

use App\Models\User;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\User as SocialiteUser;

// ============================================================
// Helpers
// ============================================================

function makeSocialiteUser(string $id = '123456789', string $email = 'google@example.com'): SocialiteUser
{
    $socialiteUser = new SocialiteUser;
    $socialiteUser->map([
        'id' => $id,
        'email' => $email,
        'name' => 'Test User',
    ]);

    return $socialiteUser;
}

function mockSocialiteDriver(SocialiteUser $socialiteUser): void
{
    $provider = Mockery::mock(GoogleProvider::class);
    $provider->shouldReceive('user')->andReturn($socialiteUser);

    $socialite = Mockery::mock(SocialiteFactory::class);
    $socialite->shouldReceive('driver')->with('google')->andReturn($provider);

    app()->instance(SocialiteFactory::class, $socialite);
}

// ============================================================
// Redirect
// ============================================================

test('GET /auth/google redirects to Google OAuth', function () {
    $response = $this->get('/auth/google');

    $response->assertRedirect();
    expect($response->headers->get('Location'))->toContain('accounts.google.com');
});

// ============================================================
// Callback — new user
// ============================================================

test('Google callback creates new user and logs in', function () {
    seedPlans();

    $socialiteUser = makeSocialiteUser('google-new-123', 'newuser@example.com');
    mockSocialiteDriver($socialiteUser);

    $response = $this->get('/auth/google/callback');

    $response->assertRedirect();
    expect($response->headers->get('Location'))->not->toContain('social_error');

    $this->assertDatabaseHas('users', [
        'email' => 'newuser@example.com',
        'google_id' => 'google-new-123',
    ]);

    $user = User::where('email', 'newuser@example.com')->first();
    expect($user->email_verified_at)->not->toBeNull();
    expect($user->hostSubscription)->not->toBeNull();
});

// ============================================================
// Callback — existing user by google_id
// ============================================================

test('Google callback logs in existing user matched by google_id', function () {
    seedPlans();

    $existing = User::factory()->create([
        'email' => 'existing@example.com',
        'google_id' => 'google-existing-456',
        'email_verified_at' => now(),
    ]);

    $socialiteUser = makeSocialiteUser('google-existing-456', 'existing@example.com');
    mockSocialiteDriver($socialiteUser);

    $response = $this->get('/auth/google/callback');

    $response->assertRedirect();
    expect($response->headers->get('Location'))->not->toContain('social_error');

    // No duplicate user created
    expect(User::where('email', 'existing@example.com')->count())->toBe(1);
});

// ============================================================
// Callback — existing user by email (auto-link)
// ============================================================

test('Google callback links google_id to existing email-password user', function () {
    seedPlans();

    $existing = User::factory()->create([
        'email' => 'linked@example.com',
        'google_id' => null,
        'email_verified_at' => now(),
    ]);

    $socialiteUser = makeSocialiteUser('google-link-789', 'linked@example.com');
    mockSocialiteDriver($socialiteUser);

    $response = $this->get('/auth/google/callback');

    $response->assertRedirect();
    expect($response->headers->get('Location'))->not->toContain('social_error');

    $existing->refresh();
    expect($existing->google_id)->toBe('google-link-789');
    expect(User::where('email', 'linked@example.com')->count())->toBe(1);
});

// ============================================================
// Callback — Socialite exception (e.g. user denied access)
// ============================================================

test('Google callback redirects with error when Socialite throws', function () {
    $provider = Mockery::mock(GoogleProvider::class);
    $provider->shouldReceive('user')->andThrow(new Exception('Access denied'));

    $socialite = Mockery::mock(SocialiteFactory::class);
    $socialite->shouldReceive('driver')->with('google')->andReturn($provider);

    app()->instance(SocialiteFactory::class, $socialite);

    $response = $this->get('/auth/google/callback');

    $response->assertRedirect();
    expect($response->headers->get('Location'))->toContain('social_error=google_failed');
});
