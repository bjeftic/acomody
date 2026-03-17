<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\DeletionRequest;
use App\Services\DeletionRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DeletionRequestController extends Controller
{
    public function __construct(private readonly DeletionRequestService $deletionRequestService) {}

    public function index(Request $request): View
    {
        $status = $request->get('status', 'pending');

        $deletionRequests = DeletionRequest::with(['user', 'processedBy'])
            ->where('status', $status)
            ->latest()
            ->paginate(25);

        return view('super-admin.deletion-requests.index', compact('deletionRequests', 'status'));
    }

    public function approve(int $id): RedirectResponse
    {
        $deletionRequest = DeletionRequest::findOrFail($id);

        if (! $deletionRequest->isPending()) {
            return back()->with('alert-warning', 'Request has already been processed.');
        }

        $this->deletionRequestService->approve($deletionRequest, Auth::user());

        return back()->with('alert-success', 'Deletion request approved and host account deleted.');
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $deletionRequest = DeletionRequest::findOrFail($id);

        if (! $deletionRequest->isPending()) {
            return back()->with('alert-warning', 'Request has already been processed.');
        }

        $this->deletionRequestService->reject($deletionRequest, Auth::user(), $request->input('reason'));

        return back()->with('alert-success', 'Deletion request rejected.');
    }
}
