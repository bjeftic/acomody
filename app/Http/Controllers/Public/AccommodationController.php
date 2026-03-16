<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccommodationResource;
use App\Http\Support\ApiResponse;
use App\Models\Accommodation;
use App\Services\AccommodationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AccommodationController extends Controller
{
    public function __construct(private readonly AccommodationService $accommodationService) {}

    public function show(Accommodation $accommodation): JsonResponse
    {
        return ApiResponse::success(
            'Accommodation retrieved successfully',
            new AccommodationResource($this->accommodationService->fetchAccommodation($accommodation->id))
        );
    }

    public function blockedDates(string $accommodationId): JsonResponse
    {
        $bookings = DB::table('bookings')
            ->where('accommodation_id', $accommodationId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereNotNull('check_in')
            ->whereNotNull('check_out')
            ->get(['check_in', 'check_out']);

        $periods = DB::table('availability_periods')
            ->where('available_id', $accommodationId)
            ->where('available_type', 'App\\Models\\Accommodation')
            ->whereIn('status', ['blocked', 'booked', 'closed'])
            ->get(['start_date', 'end_date']);

        $ranges = [];

        foreach ($bookings as $booking) {
            $ranges[] = ['start' => $booking->check_in, 'end' => $booking->check_out];
        }

        foreach ($periods as $period) {
            $ranges[] = ['start' => $period->start_date, 'end' => $period->end_date];
        }

        return response()->json(['data' => $ranges]);
    }
}
