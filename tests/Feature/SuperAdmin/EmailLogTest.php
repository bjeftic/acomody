<?php

use App\Enums\Email\EmailStatus;
use App\Listeners\Email\LogEmailSending;
use App\Listeners\Email\LogEmailSent;
use App\Models\EmailLog;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Mail\SentMessage as IlluminateSentMessage;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage as SymfonySentMessage;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as SymfonyEmail;

// ============================================================
// Access control
// ============================================================

it('blocks guests from email logs index', function () {
    $this->get(route('admin.email-logs.index'))->assertRedirect();
});

it('allows superadmin to view email logs index', function () {
    superadmin();
    $this->get(route('admin.email-logs.index'))->assertOk();
});

it('allows superadmin to view email log detail', function () {
    superadmin();

    $log = EmailLog::create([
        'recipient_email' => 'test@example.com',
        'subject' => 'Test Subject',
        'status' => EmailStatus::Sent,
        'sent_at' => now(),
    ]);

    $this->get(route('admin.email-logs.show', $log))->assertOk();
});

// ============================================================
// Index filtering
// ============================================================

it('shows all email logs', function () {
    superadmin();

    EmailLog::create(['recipient_email' => 'a@example.com', 'subject' => 'Alpha', 'status' => EmailStatus::Sent]);
    EmailLog::create(['recipient_email' => 'b@example.com', 'subject' => 'Beta', 'status' => EmailStatus::Failed]);

    $this->get(route('admin.email-logs.index'))
        ->assertOk()
        ->assertSee('a@example.com')
        ->assertSee('b@example.com');
});

it('filters email logs by sent status', function () {
    superadmin();

    EmailLog::create(['recipient_email' => 'sent@example.com', 'subject' => 'Sent Email', 'status' => EmailStatus::Sent]);
    EmailLog::create(['recipient_email' => 'failed@example.com', 'subject' => 'Failed Email', 'status' => EmailStatus::Failed]);

    $this->get(route('admin.email-logs.index', ['status' => 'sent']))
        ->assertOk()
        ->assertSee('sent@example.com')
        ->assertDontSee('failed@example.com');
});

it('searches email logs by recipient email', function () {
    superadmin();

    EmailLog::create(['recipient_email' => 'john@example.com', 'subject' => 'Hi John', 'status' => EmailStatus::Sent]);
    EmailLog::create(['recipient_email' => 'jane@example.com', 'subject' => 'Hi Jane', 'status' => EmailStatus::Sent]);

    $this->get(route('admin.email-logs.index', ['search' => 'john']))
        ->assertOk()
        ->assertSee('john@example.com')
        ->assertDontSee('jane@example.com');
});

it('searches email logs by subject', function () {
    superadmin();

    EmailLog::create(['recipient_email' => 'user@example.com', 'subject' => 'Booking Confirmed', 'status' => EmailStatus::Sent]);
    EmailLog::create(['recipient_email' => 'user@example.com', 'subject' => 'Password Reset', 'status' => EmailStatus::Sent]);

    $this->get(route('admin.email-logs.index', ['search' => 'Booking']))
        ->assertOk()
        ->assertSee('Booking Confirmed')
        ->assertDontSee('Password Reset');
});

// ============================================================
// Event listeners — LogEmailSending
// ============================================================

it('creates a pending email log when message is sending', function () {
    $symfonyEmail = (new SymfonyEmail)
        ->to('recipient@example.com')
        ->subject('Test Subject')
        ->html('<p>Test body</p>');

    $event = new MessageSending($symfonyEmail, []);

    (new LogEmailSending)->handle($event);

    $log = EmailLog::first();

    expect($log)->not->toBeNull()
        ->and($log->recipient_email)->toBe('recipient@example.com')
        ->and($log->subject)->toBe('Test Subject')
        ->and($log->status)->toBe(EmailStatus::Pending);
});

it('adds X-Acomody-Log-Id header to the outgoing message', function () {
    $symfonyEmail = (new SymfonyEmail)
        ->to('recipient@example.com')
        ->subject('Header Test')
        ->html('<p>Body</p>');

    $event = new MessageSending($symfonyEmail, []);

    (new LogEmailSending)->handle($event);

    $logId = $symfonyEmail->getHeaders()->get('X-Acomody-Log-Id')?->getValue();
    expect($logId)->not->toBeNull();

    $log = EmailLog::find((int) $logId);
    expect($log)->not->toBeNull();
});

// ============================================================
// Event listeners — LogEmailSent
// ============================================================

it('marks email log as sent when message is delivered', function () {
    $log = EmailLog::create([
        'recipient_email' => 'user@example.com',
        'subject' => 'Delivery Test',
        'status' => EmailStatus::Pending,
    ]);

    $symfonyEmail = (new SymfonyEmail)
        ->from('from@example.com')
        ->to('user@example.com')
        ->subject('Delivery Test')
        ->html('<p>Hi</p>');

    $symfonyEmail->getHeaders()->addTextHeader('X-Acomody-Log-Id', (string) $log->id);

    $envelope = new Envelope(
        new Address('from@example.com'),
        [new Address('user@example.com')],
    );

    $sentMessage = new IlluminateSentMessage(new SymfonySentMessage($symfonyEmail, $envelope));
    $event = new MessageSent($sentMessage, []);

    (new LogEmailSent)->handle($event);

    $log->refresh();

    expect($log->status)->toBe(EmailStatus::Sent)
        ->and($log->sent_at)->not->toBeNull();
});

it('does not crash when X-Acomody-Log-Id header is missing', function () {
    $symfonyEmail = (new SymfonyEmail)
        ->from('from@example.com')
        ->to('user@example.com')
        ->subject('No Header')
        ->html('<p>Hi</p>');

    $envelope = new Envelope(
        new Address('from@example.com'),
        [new Address('user@example.com')],
    );

    $sentMessage = new IlluminateSentMessage(new SymfonySentMessage($symfonyEmail, $envelope));
    $event = new MessageSent($sentMessage, []);

    expect(fn () => (new LogEmailSent)->handle($event))->not->toThrow(Throwable::class);
});
