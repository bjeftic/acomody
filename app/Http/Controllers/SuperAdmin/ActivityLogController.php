<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\Activity\ActivityEvent;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController
{
    public function index(Request $request): View
    {
        /** @var string $search */
        $search = $request->search ?? '';
        $event = $request->event ?? '';
        $userId = $request->user_id ?? '';
        $dateFrom = $request->date_from ?? '';
        $dateTo = $request->date_to ?? '';

        $query = ActivityLog::with(['subject', 'causer'])
            ->latest('id');

        if (! empty($search)) {
            $query->search($search);
        }

        if ($event !== '' && $eventEnum = ActivityEvent::tryFrom($event)) {
            $query->event($eventEnum);
        }

        if ($userId !== '') {
            $query->forUser((int) $userId);
        }

        if (! empty($dateFrom)) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if (! empty($dateTo)) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $logs = $query->paginate(50)->appends($request->only(['search', 'event', 'user_id', 'date_from', 'date_to', 'page']));

        $userOptions = User::query()
            ->when(! empty($userId), fn ($q) => $q->where('id', $userId))
            ->orderBy('email')
            ->limit(100)
            ->get(['id', 'email']);

        return view('super-admin.activity-logs.index', [
            'logs' => $logs,
            'search' => $search,
            'event' => $event,
            'userId' => $userId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'events' => ActivityEvent::cases(),
            'userOptions' => $userOptions,
            'eventGroups' => [
                'Users' => ActivityEvent::userEvents(),
                'Accommodations' => ActivityEvent::accommodationEvents(),
                'Bookings' => ActivityEvent::bookingEvents(),
                'Payments' => ActivityEvent::paymentEvents(),
                'Emails' => ActivityEvent::emailEvents(),
            ],
        ]);
    }

    public function user(User $user): View
    {
        $logs = ActivityLog::with(['subject', 'causer'])
            ->forUser($user->id)
            ->latest('id')
            ->paginate(30);

        return view('super-admin.activity-logs.user', compact('user', 'logs'));
    }
}
