<?php

namespace App\Services;

use App\Enums\Accommodation\BookingType;
use App\Enums\Booking\BookingStatus;
use App\Enums\Booking\PaymentStatus;
use App\Events\Booking\BookingCancelled;
use App\Events\Booking\BookingConfirmed;
use App\Events\Booking\BookingCreated;
use App\Events\Booking\BookingDeclined;
use App\Models\Accommodation;
use App\Models\AvailabilityPeriod;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function __construct(
        private readonly AvailabilityService $availabilityService,
        private readonly PricingService $pricingService,
    ) {}

    // ============================================
    // AVAILABILITY & PRICE (public helpers)
    // ============================================

    /**
     * Check availability for an accommodation over a date range.
     */
    public function checkAvailability(
        Accommodation $accommodation,
        Carbon $checkIn,
        Carbon $checkOut
    ): array {
        return $this->availabilityService->checkAvailability(
            Accommodation::class,
            $accommodation->id,
            $checkIn,
            $checkOut
        );
    }

    /**
     * Calculate full price breakdown for a stay.
     */
    public function calculatePrice(
        Accommodation $accommodation,
        Carbon $checkIn,
        Carbon $checkOut,
        int $guests,
        array $optionalFeeIds = [],
        array $guestAges = []
    ): array {
        $nights = (int) $checkIn->diffInDays($checkOut);

        return $this->pricingService->calculateTotalPrice(
            Accommodation::class,
            $accommodation->id,
            $checkIn,
            $checkOut,
            $nights,
            $guests,
            $guestAges,
            $optionalFeeIds
        );
    }

    // ============================================
    // BOOKING LIFECYCLE
    // ============================================

    /**
     * Create a new booking request.
     *
     * For instant_booking: confirms immediately and blocks dates.
     * For request_to_book: stays pending, notifies host.
     */
    public function createBooking(Accommodation $accommodation, User $guest, array $data): Booking
    {
        $checkIn = Carbon::parse($data['check_in']);
        $checkOut = Carbon::parse($data['check_out']);
        $nights = (int) $checkIn->diffInDays($checkOut);
        $guests = (int) $data['guests'];

        // Guard: guest count
        if ($accommodation->max_guests && $guests > $accommodation->max_guests) {
            throw new \InvalidArgumentException(
                "This property allows a maximum of {$accommodation->max_guests} guests."
            );
        }

        // Guard: overlapping active bookings (pending or confirmed)
        $overlappingBooking = DB::table('bookings')
            ->where('accommodation_id', $accommodation->id)
            ->whereIn('status', [BookingStatus::PENDING->value, BookingStatus::CONFIRMED->value])
            ->where('check_in', '<', $checkOut->toDateString())
            ->where('check_out', '>', $checkIn->toDateString())
            ->exists();

        if ($overlappingBooking) {
            throw new \RuntimeException('The selected dates are not available.');
        }

        // Guard: availability (blocked/closed periods)
        $availability = $this->availabilityService->checkAvailability(
            Accommodation::class,
            $accommodation->id,
            $checkIn,
            $checkOut
        );

        if (! $availability['available']) {
            throw new \RuntimeException(
                'The selected dates are not available: '.implode(', ', $availability['reasons'])
            );
        }

        // Guard: pricing validation (min/max nights)
        $validation = $this->pricingService->validateBooking(
            Accommodation::class,
            $accommodation->id,
            $checkIn,
            $checkOut,
            $nights,
            $guests
        );

        if (! $validation['valid']) {
            throw new \InvalidArgumentException(implode(', ', $validation['errors']));
        }

        // Calculate price
        $breakdown = $this->pricingService->calculateTotalPrice(
            Accommodation::class,
            $accommodation->id,
            $checkIn,
            $checkOut,
            $nights,
            $guests,
            $data['guest_ages'] ?? [],
            $data['optional_fee_ids'] ?? []
        );

        $bookingType = $accommodation->booking_type ?? BookingType::INSTANT_BOOKING->value;

        DB::beginTransaction();
        try {
            $priceDetails = array_filter([
                'unit_prices' => $breakdown['unit_prices'] ?? null,
                'bulk_discount' => $breakdown['bulk_discount'] ?? null,
                'fees' => $breakdown['fees'] ?? null,
                'taxes' => $breakdown['taxes'] ?? null,
            ], fn ($v) => $v !== null);

            $booking = Booking::create([
                'accommodation_id' => $accommodation->id,
                'user_id' => $guest->id,
                'host_user_id' => $accommodation->user_id,
                'check_in' => $checkIn->toDateString(),
                'check_out' => $checkOut->toDateString(),
                'nights' => $nights,
                'guests' => $guests,
                'status' => BookingStatus::PENDING,
                'booking_type' => $bookingType,
                'currency' => $breakdown['currency'],
                'subtotal' => $breakdown['subtotal'],
                'fees_total' => $breakdown['fees_subtotal'] ?? 0,
                'taxes_total' => $breakdown['taxes_subtotal'] ?? 0,
                'total_price' => $breakdown['total'],
                'priceable_item_id' => $breakdown['priceable_item_id'],
                'price_details' => $priceDetails,
                'optional_fee_ids' => $data['optional_fee_ids'] ?? null,
                'payment_status' => PaymentStatus::UNPAID,
                'guest_notes' => $data['guest_notes'] ?? null,
            ]);

            // Instant booking: confirm immediately
            if ($bookingType === BookingType::INSTANT_BOOKING->value) {
                $this->confirmAndBlockDates($booking, $checkIn, $checkOut, $accommodation);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        event(new BookingCreated($booking));

        return $booking;
    }

    /**
     * Host confirms a pending booking (request_to_book).
     */
    public function confirmBooking(Booking $booking, User $host): Booking
    {
        if (! $booking->canBeConfirmedBy($host)) {
            throw new \RuntimeException('This booking cannot be confirmed.');
        }

        $checkIn = Carbon::parse($booking->check_in);
        $checkOut = Carbon::parse($booking->check_out);

        // Check dates are still available (another booking may have taken them while pending)
        $availability = $this->availabilityService->checkAvailability(
            Accommodation::class,
            $booking->accommodation_id,
            $checkIn,
            $checkOut
        );

        if (! $availability['available']) {
            throw new \RuntimeException(
                'The requested dates are no longer available: '.implode(', ', $availability['reasons'])
            );
        }

        DB::beginTransaction();
        try {
            $this->confirmAndBlockDates($booking, $checkIn, $checkOut, $booking->accommodation);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        event(new BookingConfirmed($booking));

        return $booking;
    }

    /**
     * Host declines a pending booking.
     */
    public function declineBooking(Booking $booking, User $host, ?string $reason = null): Booking
    {
        if (! $booking->canBeDeclinedBy($host)) {
            throw new \RuntimeException('This booking cannot be declined.');
        }

        $booking->update([
            'status' => BookingStatus::DECLINED,
            'declined_at' => now(),
            'decline_reason' => $reason,
        ]);

        event(new BookingDeclined($booking));

        return $booking;
    }

    /**
     * Cancel a booking (guest or host).
     */
    public function cancelBooking(Booking $booking, User $canceller, ?string $reason = null): Booking
    {
        if (! $booking->canBeCancelledBy($canceller)) {
            throw new \RuntimeException('This booking cannot be cancelled.');
        }

        $refundAmount = $this->calculateRefundAmount($booking);

        DB::beginTransaction();
        try {
            // Free up blocked dates if booking was confirmed
            if ($booking->isConfirmed() && $booking->availability_period_id) {
                AvailabilityPeriod::where('id', $booking->availability_period_id)->delete();
            }

            $booking->update([
                'status' => BookingStatus::CANCELLED,
                'cancelled_at' => now(),
                'cancelled_by_user_id' => $canceller->id,
                'cancellation_reason' => $reason,
                'refund_amount' => $refundAmount,
                'availability_period_id' => null,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        event(new BookingCancelled($booking, $canceller->id));

        return $booking;
    }

    // ============================================
    // LISTING
    // ============================================

    /**
     * All bookings for the authenticated guest.
     */
    public function getGuestBookings(User $guest, int $perPage = 15): LengthAwarePaginator
    {
        return Booking::query()
            ->with(['accommodation.primaryPhoto', 'guest'])
            ->where('user_id', $guest->id)
            ->orderByDesc('check_in')
            ->paginate($perPage);
    }

    /**
     * All bookings across the host's properties.
     */
    public function getHostBookings(User $host, int $perPage = 15, ?string $status = null): LengthAwarePaginator
    {
        $paginator = Booking::query()
            ->with(['accommodation.primaryPhoto'])
            ->where('host_user_id', $host->id)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('check_in')
            ->paginate($perPage);

        $guestIds = collect($paginator->items())->pluck('user_id')->unique()->filter()->values();

        $guestMap = DB::table('users')
            ->leftJoin('user_profiles', 'user_profiles.user_id', '=', 'users.id')
            ->whereIn('users.id', $guestIds)
            ->get(['users.id', 'users.email', 'user_profiles.first_name', 'user_profiles.last_name'])
            ->keyBy('id');

        foreach ($paginator->items() as $booking) {
            $booking->guest_info = $guestMap->get($booking->user_id);
        }

        return $paginator;
    }

    // ============================================
    // PRIVATE HELPERS
    // ============================================

    /**
     * Mark availability as booked and update booking to confirmed.
     */
    private function confirmAndBlockDates(
        Booking $booking,
        Carbon $checkIn,
        Carbon $checkOut,
        Accommodation $accommodation
    ): void {
        $period = $this->availabilityService->markAsBooked(
            Accommodation::class,
            $accommodation->id,
            $checkIn,
            $checkOut,
            "Booking ID: {$booking->id}"
        );

        $booking->update([
            'status' => BookingStatus::CONFIRMED,
            'confirmed_at' => now(),
            'availability_period_id' => $period->id,
        ]);
    }

    /**
     * Calculate refund amount based on accommodation's cancellation policy.
     */
    public function calculateRefundAmount(Booking $booking): float
    {
        if ($booking->payment_status !== PaymentStatus::PAID) {
            return 0.0;
        }

        if (! $booking->isConfirmed()) {
            return 0.0;
        }

        $daysUntilCheckIn = (int) now()->startOfDay()->diffInDays(
            Carbon::parse($booking->check_in)->startOfDay(),
            absolute: false
        );

        $total = $booking->total_price;

        return match ($booking->accommodation->cancellation_policy) {
            'flexible' => $daysUntilCheckIn >= 1 ? $total : 0.0,
            'moderate' => $daysUntilCheckIn >= 5 ? $total : ($daysUntilCheckIn >= 0 ? $total * 0.5 : 0.0),
            'firm' => $daysUntilCheckIn >= 30 ? $total * 0.5 : 0.0,
            'strict' => $daysUntilCheckIn >= 60 ? $total * 0.5 : 0.0,
            'non_refundable' => 0.0,
            default => 0.0,
        };
    }

    public function fetchBooking(string $bookingId): ?Booking
    {
        return Booking::query()
            ->with(['accommodation.primaryPhoto'])
            ->find($bookingId);
    }
}
