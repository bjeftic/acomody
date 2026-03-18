<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Http\Resources\AvailabilityPeriodResource;
use App\Http\Support\ApiResponse;
use App\Models\Accommodation;
use App\Models\AvailabilityPeriod;
use Illuminate\Http\JsonResponse;

class AvailabilityPeriodController extends Controller
{
    public function index(): JsonResponse
    {
        $user = userOrFail();

        $accommodationIds = Accommodation::query()
            ->where('user_id', $user->id)
            ->pluck('id');

        $periods = AvailabilityPeriod::query()
            ->with('icalCalendar')
            ->whereIn('available_id', $accommodationIds)
            ->where('available_type', 'App\Models\Accommodation')
            ->whereIn('status', ['blocked', 'closed'])
            ->orderBy('start_date')
            ->get();

        return ApiResponse::success(
            'Blocked periods retrieved successfully',
            AvailabilityPeriodResource::collection($periods)
        );
    }
}
