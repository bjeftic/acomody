<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Http\Requests\IcalCalendar\StoreRequest;
use App\Http\Requests\IcalCalendar\UpdateRequest;
use App\Http\Resources\IcalCalendarResource;
use App\Http\Support\ApiResponse;
use App\Models\Accommodation;
use App\Models\IcalCalendar;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class IcalCalendarController extends Controller
{
    /**
     * List all external iCal calendars for an accommodation.
     */
    public function index(Accommodation $accommodation): JsonResponse
    {
        $this->authorizeOwnership($accommodation);

        $calendars = $accommodation->icalCalendars()->get();

        return ApiResponse::success(
            'iCal calendars retrieved successfully',
            IcalCalendarResource::collection($calendars)
        );
    }

    /**
     * Add a new external iCal calendar.
     */
    public function store(StoreRequest $request, Accommodation $accommodation): JsonResponse
    {
        $this->authorizeOwnership($accommodation);

        $calendar = $accommodation->icalCalendars()->create($request->validated());

        return ApiResponse::success(
            'iCal calendar added successfully',
            new IcalCalendarResource($calendar),
            statusCode: 201
        );
    }

    /**
     * Update an external iCal calendar.
     */
    public function update(UpdateRequest $request, Accommodation $accommodation, IcalCalendar $icalCalendar): JsonResponse
    {
        $this->authorizeOwnership($accommodation);

        $icalCalendar->update($request->validated());

        return ApiResponse::success(
            'iCal calendar updated successfully',
            new IcalCalendarResource($icalCalendar->fresh())
        );
    }

    /**
     * Remove an external iCal calendar and all imported availability periods.
     */
    public function destroy(Accommodation $accommodation, IcalCalendar $icalCalendar): JsonResponse
    {
        $this->authorizeOwnership($accommodation);

        $icalCalendar->availabilityPeriods()->delete();
        $icalCalendar->delete();

        return ApiResponse::success('iCal calendar removed successfully');
    }

    /**
     * Regenerate the iCal export token for an accommodation.
     */
    public function regenerateToken(Accommodation $accommodation): JsonResponse
    {
        $this->authorizeOwnership($accommodation);

        do {
            $token = Str::random(64);
        } while (Accommodation::query()->where('ical_token', $token)->exists());

        $accommodation->update(['ical_token' => $token]);

        return ApiResponse::success(
            'iCal token regenerated successfully',
            null,
            ['ical_token' => $accommodation->ical_token]
        );
    }

    private function authorizeOwnership(Accommodation $accommodation): void
    {
        if ($accommodation->user_id !== userOrFail()->id) {
            abort(403, 'You do not own this accommodation.');
        }
    }
}
