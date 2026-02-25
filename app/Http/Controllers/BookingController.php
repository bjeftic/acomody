<?php

namespace App\Http\Controllers;

use App\Http\Requests\Booking\CancelRequest;
use App\Http\Requests\Booking\StoreRequest;
use App\Http\Resources\BookingResource;
use App\Http\Support\ApiResponse;
use App\Models\Accommodation;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    public function __construct(private readonly BookingService $bookingService) {}

    /**
     * List the authenticated guest's bookings.
     */
    public function index(): JsonResponse
    {
        $bookings = $this->bookingService->getGuestBookings(userOrFail());

        return ApiResponse::success(
            'Bookings retrieved successfully',
            BookingResource::collection($bookings->items()),
            [
                'current_page' => $bookings->currentPage(),
                'last_page'    => $bookings->lastPage(),
                'per_page'     => $bookings->perPage(),
                'total'        => $bookings->total(),
            ]
        );
    }

    /**
     * Show a single booking (guest must own it).
     */
    public function show(Booking $booking): JsonResponse
    {
        if ($booking->user_id !== userOrFail()->id) {
            return ApiResponse::forbidden('You do not have access to this booking.');
        }

        $booking->load(['accommodation', 'guest']);

        return ApiResponse::success(
            'Booking retrieved successfully',
            new BookingResource($booking)
        );
    }

    /**
     * Create a new booking.
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $accommodation = Accommodation::findOrFail($request->accommodation_id);

        try {
            $booking = $this->bookingService->createBooking(
                $accommodation,
                userOrFail(),
                $request->validated()
            );

            return ApiResponse::created(
                $booking->booking_type === 'instant_booking'
                    ? 'Booking confirmed successfully'
                    : 'Booking request submitted successfully',
                new BookingResource($booking)
            );
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), null, null, 422);
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), null, null, 409);
        } catch (\Throwable $e) {
            Log::error('Booking creation failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return ApiResponse::error('Failed to create booking. Please try again.', null, null, 500);
        }
    }

    /**
     * Cancel a booking (guest).
     */
    public function cancel(CancelRequest $request, Booking $booking): JsonResponse
    {
        try {
            $booking = $this->bookingService->cancelBooking(
                $booking,
                userOrFail(),
                $request->input('reason')
            );

            return ApiResponse::success(
                'Booking cancelled successfully',
                new BookingResource($booking)
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), null, null, 409);
        } catch (\Throwable $e) {
            Log::error('Booking cancellation failed', ['booking_id' => $booking->id, 'error' => $e->getMessage()]);
            return ApiResponse::error('Failed to cancel booking.', null, null, 500);
        }
    }
}
