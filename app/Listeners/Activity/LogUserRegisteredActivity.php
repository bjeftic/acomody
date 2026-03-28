<?php

namespace App\Listeners\Activity;

use App\Enums\Activity\ActivityEvent;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Auth\Events\Registered;

class LogUserRegisteredActivity
{
    public function handle(Registered $event): void
    {
        /** @var User $user */
        $user = $event->user;

        ActivityLogService::log(
            event: ActivityEvent::UserRegistered,
            description: "New user registered: {$user->email}",
            subject: $user,
            causer: null,
            properties: ['email' => $user->email],
        );
    }
}
