<?php

namespace App\Mail\Auth;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerifyEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $verificationUrl,
    ) {
        $this->locale($user->preferred_language ?? 'en');
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('mail.verify_email.subject'));
    }

    public function content(): Content
    {
        return new Content(view: 'mail.auth.verify-email');
    }
}
