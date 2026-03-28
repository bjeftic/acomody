<?php

namespace App\Listeners\Email;

use App\Enums\Activity\ActivityEvent;
use App\Enums\Email\EmailStatus;
use App\Models\EmailLog;
use App\Services\ActivityLogService;

class LogEmailFailed
{
    /**
     * Mark any email log that stayed as "pending" and has a matching failed job.
     * Called by wrapping the send in a try/catch inside LogEmailSending.
     * This listener handles the Symfony mailer exception event alternative:
     * we catch failures by hooking into the queue's JobFailed event.
     */
    public static function markFailed(int $logId, string $errorMessage): void
    {
        $updated = EmailLog::where('id', $logId)
            ->where('status', EmailStatus::Pending->value)
            ->update([
                'status' => EmailStatus::Failed->value,
                'error_message' => $errorMessage,
            ]);

        if ($updated) {
            $emailLog = EmailLog::find($logId);

            if ($emailLog) {
                ActivityLogService::log(
                    event: ActivityEvent::EmailFailed,
                    description: "Email failed to {$emailLog->recipient_email}: {$emailLog->subject}",
                    subject: $emailLog,
                    properties: [
                        'recipient_email' => $emailLog->recipient_email,
                        'subject' => $emailLog->subject,
                        'error' => $errorMessage,
                    ],
                );
            }
        }
    }
}
