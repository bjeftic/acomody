<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\CancelRequest;
use App\Http\Requests\Booking\DeclineRequest;
use App\Http\Resources\BookingResource;
use App\Http\Support\ApiResponse;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    public function __construct(private readonly BookingService $bookingService) {}

    /**
     * List all bookings across the host's properties.
     */
    public function index(Request $request): JsonResponse
    {
        $status   = $request->query('status');
        $bookings = $this->bookingService->getHostBookings(userOrFail(), status: $status);

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
     * Show a booking detail (host must own the accommodation).
     */
    public function show(Booking $booking): JsonResponse
    {
        if ($booking->host_user_id !== userOrFail()->id) {
            return ApiResponse::forbidden('You do not have access to this booking.');
        }

        $booking->load(['accommodation', 'guest']);

        return ApiResponse::success(
            'Booking retrieved successfully',
            new BookingResource($booking)
        );
    }

    /**
     * Confirm a pending booking (request_to_book flow).
     */
    public function confirm(Booking $booking): JsonResponse
    {
        try {
            $booking = $this->bookingService->confirmBooking($booking, userOrFail());

            return ApiResponse::success(
                'Booking confirmed successfully',
                new BookingResource($booking)
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), null, null, 409);
        } catch (\Throwable $e) {
            Log::error('Booking confirmation failed', ['booking_id' => $booking->id, 'error' => $e->getMessage()]);
            return ApiResponse::error('Failed to confirm booking.', null, null, 500);
        }
    }

    /**
     * Decline a pending booking.
     */
    public function decline(DeclineRequest $request, Booking $booking): JsonResponse
    {
        try {
            $booking = $this->bookingService->declineBooking(
                $booking,
                userOrFail(),
                $request->input('reason')
            );

            return ApiResponse::success(
                'Booking declined',
                new BookingResource($booking)
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), null, null, 409);
        } catch (\Throwable $e) {
            Log::error('Booking decline failed', ['booking_id' => $booking->id, 'error' => $e->getMessage()]);
            return ApiResponse::error('Failed to decline booking.', null, null, 500);
        }
    }

    /**
     * Cancel a confirmed booking (host-initiated).
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
            Log::error('Booking host-cancel failed', ['booking_id' => $booking->id, 'error' => $e->getMessage()]);
            return ApiResponse::error('Failed to cancel booking.', null, null, 500);
        }
    }
}
