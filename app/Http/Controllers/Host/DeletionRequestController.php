<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Http\Support\ApiResponse;
use App\Models\Accommodation;
use App\Services\DeletionRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DeletionRequestController extends Controller
{
    public function __construct(private readonly DeletionRequestService $deletionRequestService) {}

    public function requestHostAccountDeletion(): JsonResponse
    {
        $user = Auth::user();

        if (! $user->hostProfile) {
            throw new HttpException(404, 'No host account found.');
        }

        $this->deletionRequestService->requestHostAccountDeletion($user);

        return ApiResponse::success('Deletion request submitted. Our team will process it shortly.');
    }

    public function requestAccommodationDeletion(Accommodation $accommodation): JsonResponse
    {
        $user = Auth::user();

        if ($accommodation->user_id !== $user->id) {
            throw new HttpException(403, 'You do not own this accommodation.');
        }

        $this->deletionRequestService->requestAccommodationDeletion($user, $accommodation);

        return ApiResponse::success('Accommodation deletion request submitted. Our team will process it shortly.');
    }
}
