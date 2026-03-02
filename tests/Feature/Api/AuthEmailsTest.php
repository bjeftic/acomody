<?php

use App\Mail\Auth\ResetPasswordMail;
use App\Mail\Auth\VerifyEmailMail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;

// ============================================================
// Verify Email
// ============================================================

describe('sendEmailVerificationNotification', function () {

    it('queues VerifyEmailMail to the user email', function () {
        Mail::fake();

        $user = authenticatedUser(['email_verified_at' => null]);

        $user->sendEmailVerificationNotification();

        Mail::assertQueued(VerifyEmailMail::class, fn ($mail) => $mail->hasTo($user->email));
    });

    it('includes a signed verification URL', function () {
        Mail::fake();

        $user = authenticatedUser(['email_verified_at' => null]);

        $user->sendEmailVerificationNotification();

        Mail::assertQueued(VerifyEmailMail::class, fn ($mail) => str_contains($mail->verificationUrl, route('verification.verify', [
            'id' => $user->getKey(),
            'hash' => sha1($user->email),
        ], false))
        );
    });

    it('URL contains the user id and email hash', function () {
        Mail::fake();

        $user = authenticatedUser(['email_verified_at' => null]);

        $user->sendEmailVerificationNotification();

        Mail::assertQueued(VerifyEmailMail::class, function ($mail) use ($user) {
            return str_contains($mail->verificationUrl, (string) $user->getKey())
                && str_contains($mail->verificationUrl, sha1($user->email));
        });
    });

    it('is triggered by the Registered event for unverified users', function () {
        Mail::fake();

        $user = authenticatedUser(['email_verified_at' => null]);

        event(new Registered($user));

        Mail::assertQueued(VerifyEmailMail::class, fn ($mail) => $mail->hasTo($user->email));
    });

    it('is not sent when user is already verified', function () {
        Mail::fake();

        $user = authenticatedUser(['email_verified_at' => now()]);

        event(new Registered($user));

        Mail::assertNotQueued(VerifyEmailMail::class);
    });

});

// ============================================================
// Reset Password
// ============================================================

describe('sendPasswordResetNotification', function () {

    it('queues ResetPasswordMail to the user email', function () {
        Mail::fake();

        $user = authenticatedUser();

        $user->sendPasswordResetNotification('test-token');

        Mail::assertQueued(ResetPasswordMail::class, fn ($mail) => $mail->hasTo($user->email));
    });

    it('includes the reset token in the URL', function () {
        Mail::fake();

        $user = authenticatedUser();

        $user->sendPasswordResetNotification('my-secret-token');

        Mail::assertQueued(ResetPasswordMail::class, fn ($mail) => str_contains($mail->resetUrl, 'my-secret-token')
        );
    });

    it('includes the user email in the URL', function () {
        Mail::fake();

        $user = authenticatedUser();

        $user->sendPasswordResetNotification('test-token');

        Mail::assertQueued(ResetPasswordMail::class, fn ($mail) => str_contains($mail->resetUrl, urlencode($user->email))
        );
    });

    it('includes the reset-password path in the URL', function () {
        Mail::fake();

        $user = authenticatedUser();

        $user->sendPasswordResetNotification('test-token');

        Mail::assertQueued(ResetPasswordMail::class, fn ($mail) => str_contains($mail->resetUrl, '/reset-password')
        );
    });

});
