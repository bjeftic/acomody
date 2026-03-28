<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\Email\EmailStatus;
use App\Models\EmailLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailLogController
{
    public function index(Request $request): View
    {
        /** @var string $search */
        $search = $request->search ?? '';
        $status = $request->status ?? '';

        $query = EmailLog::query()->latest('id');

        if (! empty($search)) {
            $query->search($search);
        }

        if ($status !== '' && $statusEnum = EmailStatus::tryFrom($status)) {
            $query->status($statusEnum);
        }

        $emailLogs = $query->paginate(50)->appends($request->only(['search', 'status', 'page']));

        return view('super-admin.email-logs.index', [
            'emailLogs' => $emailLogs,
            'search' => $search,
            'status' => $status,
            'statuses' => EmailStatus::cases(),
        ]);
    }

    public function show(EmailLog $emailLog): View
    {
        return view('super-admin.email-logs.show', compact('emailLog'));
    }
}
