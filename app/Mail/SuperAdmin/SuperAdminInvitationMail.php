<?php

namespace App\Mail\SuperAdmin;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SuperAdminInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $setPasswordUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'You have been invited as a Superadmin — Set your password');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.super-admin.invitation');
    }
}
