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
     *
     * @OA\Get(
     *     path="/bookings",
     *     operationId="getGuestBookings",
     *     tags={"Booking"},
     *     summary="List guest bookings",
     *     description="Returns a paginated list of bookings made by the authenticated guest.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *
     *         @OA\Schema(type="integer", minimum=1, default=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=15)
     *     ),
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by booking status",
     *         required=false,
     *
     *         @OA\Schema(
     *             type="string",
     *             enum={"pending","confirmed","declined","cancelled","completed"}
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Bookings retrieved successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Bookings retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *
     *                 @OA\Items(ref="#/components/schemas/Booking")
     *             ),
     *
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=3),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=42)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(): JsonResponse
    {
        $bookings = $this->bookingService->getGuestBookings(userOrFail());

        return ApiResponse::success(
            'Bookings retrieved successfully',
            BookingResource::collection($bookings),
            [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total(),
            ]
        );
    }

    /**
     * Show a single booking (guest must own it).
     *
     * @OA\Get(
     *     path="/bookings/{booking}",
     *     operationId="getGuestBooking",
     *     tags={"Booking"},
     *     summary="Get a single guest booking",
     *     description="Returns the details of a specific booking. Only the guest who made the booking can access it.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="booking",
     *         in="path",
     *         required=true,
     *         description="Booking ID (ULID)",
     *
     *         @OA\Schema(type="string", format="ulid", example="01jst4xa7fmszvxmgs382q910a")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Booking retrieved successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Booking retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Booking")
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden — booking belongs to another user"),
     *     @OA\Response(response=404, description="Booking not found")
     * )
     */
    public function show(Booking $booking): JsonResponse
    {
        return ApiResponse::success(
            'Booking retrieved successfully',
            new BookingResource($this->bookingService->fetchBooking($booking->id))
        );
    }

    /**
     * Create a new booking.
     *
     * @OA\Post(
     *     path="/bookings",
     *     operationId="createGuestBooking",
     *     tags={"Booking"},
     *     summary="Create a booking",
     *     description="Creates a new booking request for an accommodation. Returns 201 with the booking. For instant-booking accommodations the booking is confirmed immediately; for request-to-book it is marked pending.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"accommodation_id","check_in","check_out","guests"},
     *
     *             @OA\Property(
     *                 property="accommodation_id",
     *                 type="string",
     *                 format="ulid",
     *                 example="01jst4xa7fmszvxmgs382q910a",
     *                 description="ID of the accommodation to book"
     *             ),
     *             @OA\Property(
     *                 property="check_in",
     *                 type="string",
     *                 format="date",
     *                 example="2026-06-01",
     *                 description="Check-in date (today or future)"
     *             ),
     *             @OA\Property(
     *                 property="check_out",
     *                 type="string",
     *                 format="date",
     *                 example="2026-06-05",
     *                 description="Check-out date (must be after check_in)"
     *             ),
     *             @OA\Property(
     *                 property="guests",
     *                 type="integer",
     *                 minimum=1,
     *                 example=2,
     *                 description="Number of guests"
     *             ),
     *             @OA\Property(
     *                 property="guest_notes",
     *                 type="string",
     *                 nullable=true,
     *                 maxLength=1000,
     *                 example="We will arrive late in the evening.",
     *                 description="Optional note to the host"
     *             ),
     *             @OA\Property(
     *                 property="optional_fee_ids",
     *                 type="array",
     *                 nullable=true,
     *                 description="IDs of optional fees to include",
     *
     *                 @OA\Items(type="string", format="ulid")
     *             ),
     *
     *             @OA\Property(
     *                 property="guest_ages",
     *                 type="array",
     *                 nullable=true,
     *                 description="Ages of each guest",
     *
     *                 @OA\Items(type="integer", minimum=0, maximum=120)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Booking created successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Booking request submitted successfully"
     *             ),
     *             @OA\Property(property="data", ref="#/components/schemas/Booking")
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Accommodation not found"),
     *     @OA\Response(response=409, description="Conflict — dates unavailable or booking constraint violated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
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
                new BookingResource($this->bookingService->fetchBooking($booking->id))
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
     *
     * @OA\Post(
     *     path="/bookings/{booking}/cancel",
     *     operationId="cancelGuestBooking",
     *     tags={"Booking"},
     *     summary="Cancel a booking",
     *     description="Cancels an existing booking. Only the guest who made the booking can cancel it. The booking must be in a cancellable state (e.g. pending or confirmed).",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="booking",
     *         in="path",
     *         required=true,
     *         description="Booking ID (ULID)",
     *
     *         @OA\Schema(type="string", format="ulid", example="01jst4xa7fmszvxmgs382q910a")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=false,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(
     *                 property="reason",
     *                 type="string",
     *                 nullable=true,
     *                 maxLength=500,
     *                 example="Change of travel plans.",
     *                 description="Optional cancellation reason"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Booking cancelled successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Booking cancelled successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Booking")
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden — booking belongs to another user"),
     *     @OA\Response(response=409, description="Conflict — booking is not in a cancellable state")
     * )
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
