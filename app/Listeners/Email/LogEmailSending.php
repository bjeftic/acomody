<?php

namespace App\Listeners\Email;

use App\Enums\Email\EmailStatus;
use App\Models\EmailLog;
use Illuminate\Mail\Events\MessageSending;
use Symfony\Component\Mime\Address;

class LogEmailSending
{
    public function handle(MessageSending $event): void
    {
        $symfonyMessage = $event->message;

        $toAddresses = $symfonyMessage->getTo();
        $firstTo = $toAddresses[0] ?? null;

        $recipientEmail = $firstTo instanceof Address ? $firstTo->getAddress() : '';
        $recipientName = $firstTo instanceof Address ? ($firstTo->getName() ?: null) : null;

        $subject = $symfonyMessage->getSubject() ?? '';

        $log = EmailLog::create([
            'recipient_email' => $recipientEmail,
            'recipient_name' => $recipientName,
            'subject' => $subject,
            'status' => EmailStatus::Pending,
        ]);

        $symfonyMessage->getHeaders()->addTextHeader('X-Acomody-Log-Id', (string) $log->id);
    }
}
