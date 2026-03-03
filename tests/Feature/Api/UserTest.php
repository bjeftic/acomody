<?php

// ============================================================
// GET /api/users
// ============================================================

describe('GET /api/users', function () {

    it('returns 401 for unauthenticated requests', function () {
        $this->getJson(route('api.users'))
            ->assertUnauthorized();
    });

    it('returns 200 with correct JSON structure for an authenticated user', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.users'))
            ->assertSuccessful()
            ->assertJson(['success' => true, 'message' => 'User retrieved successfully.'])
            ->assertJsonStructure(['success', 'message', 'data', 'meta']);
    });

    it('returns meta with email_verified, profile_complete, and account_status keys', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.users'))
            ->assertSuccessful()
            ->assertJsonStructure(['meta' => ['email_verified', 'profile_complete', 'account_status']]);
    });

    it('returns email_verified true for a verified user', function () {
        $user = authenticatedUser(['email_verified_at' => now()]);

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.users'))
            ->assertSuccessful()
            ->assertJsonPath('meta.email_verified', true);
    });

    it('returns email_verified false for an unverified user', function () {
        $user = authenticatedUser(['email_verified_at' => null]);

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.users'))
            ->assertSuccessful()
            ->assertJsonPath('meta.email_verified', false);
    });

    it('returns account_status matching the user status field', function () {
        $user = authenticatedUser(['status' => 'active']);

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.users'))
            ->assertSuccessful()
            ->assertJsonPath('meta.account_status', 'active');
    });

});

// ============================================================
// PUT /api/users
// ============================================================

describe('PUT /api/users', function () {

    it('returns 401 for unauthenticated requests', function () {
        $this->putJson(route('api.users.update'), ['first_name' => 'John'])
            ->assertUnauthorized();
    });

    it('returns 200 and updates profile successfully', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.users.update'), [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'phone' => '+381601234567',
            ])
            ->assertSuccessful()
            ->assertJson(['success' => true, 'message' => 'Profile updated successfully.']);
    });

    it('accepts an empty body because all fields are nullable', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.users.update'), [])
            ->assertSuccessful();
    });

    it('returns 422 when first_name exceeds 255 characters', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.users.update'), ['first_name' => str_repeat('a', 256)])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'first_name']);
    });

    it('returns 422 when last_name exceeds 255 characters', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.users.update'), ['last_name' => str_repeat('a', 256)])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'last_name']);
    });

    it('returns 422 when phone exceeds 20 characters', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.users.update'), ['phone' => str_repeat('1', 21)])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'phone']);
    });

});

// ============================================================
// PUT /api/users/password
// ============================================================

describe('PUT /api/users/password', function () {

    it('returns 401 for unauthenticated requests', function () {
        $this->putJson(route('api.users.password.update'), [])
            ->assertUnauthorized();
    });

    it('returns 200 when password is updated successfully', function () {
        $user = authenticatedUser(['password' => 'OldP@ss123!']);

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.users.password.update'), [
                'current_password' => 'OldP@ss123!',
                'password' => 'NewP@ss456!',
                'confirm_password' => 'NewP@ss456!',
            ])
            ->assertSuccessful()
            ->assertJson(['success' => true, 'message' => 'Password updated successfully.']);
    });

    it('returns 422 when current_password is missing', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.users.password.update'), [
                'password' => 'NewP@ss456!',
                'confirm_password' => 'NewP@ss456!',
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'current_password']);
    });

    it('returns 422 when password is missing', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.users.password.update'), [
                'current_password' => 'OldP@ss123!',
                'confirm_password' => 'NewP@ss456!',
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'password']);
    });

    it('returns 422 when confirm_password is missing', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.users.password.update'), [
                'current_password' => 'OldP@ss123!',
                'password' => 'NewP@ss456!',
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'confirm_password']);
    });

    it('returns 422 when confirm_password does not match password', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.users.password.update'), [
                'current_password' => 'OldP@ss123!',
                'password' => 'NewP@ss456!',
                'confirm_password' => 'DifferentP@ss!',
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'confirm_password']);
    });

    it('returns 422 when new password does not meet strength requirements', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.users.password.update'), [
                'current_password' => 'OldP@ss123!',
                'password' => 'weakpassword',
                'confirm_password' => 'weakpassword',
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'password']);
    });

    it('returns 422 when current_password is incorrect', function () {
        $user = authenticatedUser(['password' => 'CorrectP@ss123!']);

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.users.password.update'), [
                'current_password' => 'WrongP@ss123!',
                'password' => 'NewP@ss456!',
                'confirm_password' => 'NewP@ss456!',
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'current_password']);
    });

});

// ============================================================
// POST /api/users/avatar
// ============================================================

describe('POST /api/users/avatar', function () {

    it('returns 401 for unauthenticated requests', function () {
        $this->postJson(route('api.users.avatar.upload'))
            ->assertUnauthorized();
    });

    it('returns 422 when no avatar file is provided', function () {
        $user = authenticatedUser();

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.users.avatar.upload'), [])
            ->assertUnprocessable()
            ->assertJsonFragment(['field' => 'avatar']);
    });

});
