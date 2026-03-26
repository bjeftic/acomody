<?php

namespace App\Http\Controllers;

use App\Http\Requests\Accommodation\IndexRequest;
use App\Http\Requests\Accommodation\UpdateRequest;
use App\Http\Requests\Booking\CalculatePriceRequest;
use App\Http\Requests\Booking\CheckAvailabilityRequest;
use App\Http\Resources\AccommodationResource;
use App\Http\Resources\PhotoResource;
use App\Http\Support\ApiResponse;
use App\Models\Accommodation;
use App\Services\AccommodationService;
use App\Services\BookingService;
use App\Services\CurrencyService;
use App\Services\PricingService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AccommodationController extends Controller
{
    protected AccommodationService $accommodationService;

    protected BookingService $bookingService;

    protected PricingService $pricingService;

    public function __construct(AccommodationService $accommodationService, BookingService $bookingService, PricingService $pricingService)
    {
        $this->accommodationService = $accommodationService;
        $this->bookingService = $bookingService;
        $this->pricingService = $pricingService;
    }

    /**
     * Get accommodations
     *
     * @OA\Get(
     *     path="/accommodations",
     *     operationId="getAccommodations",
     *     tags={"Accommodation"},
     *     summary="Get accommodations",
     *     description="Retrieves a paginated list of accommodations for the authenticated user",
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
     *     @OA\Response(
     *         response=200,
     *         description="Accommodations retrieved successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Accommodation")),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="last_page", type="integer"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="total", type="integer"),
     *             @OA\Property(property="next_page_url", type="string", nullable=true),
     *             @OA\Property(property="prev_page_url", type="string", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $accommodations = $this->accommodationService->getAccommodations(
            userOrFail()->id,
            $request->getPerPage()
        );

        return ApiResponse::success(
            'Accommodations retrieved successfully',
            AccommodationResource::collection($accommodations)
        );
    }

    /**
     * Get a specific listing by ID
     *
     * @OA\Get(
     *     path="/accommodations/{accommodation}",
     *     operationId="getAccommodationById",
     *     tags={"Accommodation"},
     *     summary="Get a specific accommodation by ID",
     *     description="Retrieves a specific accommodation for the authenticated user by its ID",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="accommodation",
     *         in="path",
     *         description="ID of the accommodation to retrieve",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Accommodation retrieved successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="data", ref="#/components/schemas/Accommodation")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Accommodation not found"
     *     )
     * )
     */
    public function show(Accommodation $accommodation): JsonResponse
    {
        $accommodation = $this->accommodationService->getAccommodationForEdit($accommodation->id);

        if (! $accommodation || $accommodation->user_id !== userOrFail()->id) {
            return ApiResponse::error('Accommodation not found', null, null, 404);
        }

        return ApiResponse::success(
            'Accommodation retrieved successfully',
            new AccommodationResource($accommodation)
        );
    }

    public function update(UpdateRequest $request, Accommodation $accommodation): JsonResponse
    {
        $validated = $request->validated();

        if (isset($validated['accommodation_type'])) {
            $accommodation = $this->accommodationService->updateAccommodation($accommodation, $validated);
        } else {
            $accommodation->update(array_intersect_key($validated, array_flip(['ical_export_active'])));
            $accommodation = $accommodation->fresh(['amenities', 'photos', 'pricing', 'beds', 'location.country']);
        }

        return ApiResponse::success(
            'Accommodation updated successfully',
            new AccommodationResource($accommodation)
        );
    }

    /**
     * Check availability for a date range.
     */
    public function checkAvailability(CheckAvailabilityRequest $request, Accommodation $accommodation): JsonResponse
    {
        $checkIn = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);

        $result = $this->bookingService->checkAvailability($accommodation, $checkIn, $checkOut);

        return ApiResponse::success('Availability checked', new class($result) extends \Illuminate\Http\Resources\Json\JsonResource
        {
            public function toArray($request): array
            {
                return $this->resource;
            }
        });
    }

    /**
     * Calculate full price breakdown for a stay.
     */
    public function calculatePrice(CalculatePriceRequest $request, Accommodation $accommodation): JsonResponse
    {
        try {
            $breakdown = $this->bookingService->calculatePrice(
                $accommodation,
                Carbon::parse($request->check_in),
                Carbon::parse($request->check_out),
                (int) $request->guests,
                $request->optional_fee_ids ?? [],
                $request->guest_ages ?? []
            );

            $userCurrency = CurrencyService::getUserCurrency();

            if ($userCurrency->code !== $breakdown['currency']) {
                $originalCurrency = $breakdown['currency'];
                $originalTotal = $breakdown['total'];
                $breakdown = $this->convertBreakdownCurrency($breakdown, $userCurrency->code);
                $breakdown['original_currency'] = $originalCurrency;
                $breakdown['original_total'] = $originalTotal;
            }

            return ApiResponse::success('Price calculated', new class($breakdown) extends \Illuminate\Http\Resources\Json\JsonResource
            {
                public function toArray($request): array
                {
                    return $this->resource;
                }
            });
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), null, null, 422);
        }
    }

    private function convertBreakdownCurrency(array $breakdown, string $toCurrency): array
    {
        $from = $breakdown['currency'];
        $convert = fn (?float $amount): ?float => $amount !== null
            ? calculatePriceInSettedCurrency($amount, $from, $toCurrency)
            : null;

        foreach (['subtotal', 'fees_subtotal', 'subtotal_before_tax', 'taxes_subtotal', 'total'] as $key) {
            $breakdown[$key] = $convert($breakdown[$key] ?? null);
        }

        $breakdown['currency'] = $toCurrency;

        foreach (['subtotal', 'fees_subtotal', 'subtotal_before_tax', 'taxes_subtotal', 'total'] as $key) {
            $breakdown["{$key}_formatted"] = $this->pricingService->formatPrice((float) ($breakdown[$key] ?? 0), $toCurrency);
        }

        if (isset($breakdown['bulk_discount']['amount'])) {
            $breakdown['bulk_discount']['amount'] = $convert($breakdown['bulk_discount']['amount']);
        }

        foreach (['mandatory', 'optional'] as $type) {
            foreach ($breakdown['fees'][$type] ?? [] as &$fee) {
                $fee['amount'] = $convert($fee['amount'] ?? 0);
            }
        }

        foreach ($breakdown['taxes'] ?? [] as &$tax) {
            $tax['amount'] = $convert($tax['amount'] ?? 0);
        }

        return $breakdown;
    }

    public function indexPhotos(Accommodation $accommodation): JsonResponse
    {
        try {
            $photos = $accommodation->photos()
                ->ordered()
                ->get();

            return ApiResponse::success(
                'Accommodation photos retrieved successfully.',
                PhotoResource::collection($photos)
            );
        } catch (Exception $e) {
            Log::error('Failed to retrieve photos', [
                'draft_id' => $accommodation->id,
                'error' => $e->getMessage(),
            ]);

            return ApiResponse::error('Failed to retrieve photos.', null, null, 500);
        }
    }
}
