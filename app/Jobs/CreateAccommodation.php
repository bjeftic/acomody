<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\AccommodationDraft;
use App\Models\Accommodation;
use App\Models\User;
use Illuminate\Queue\SerializesModels;
use App\Enums\Accommodation\AccommodationOccupation;
use App\Enums\PriceableItem\PricingType;
use App\Models\Listing;
use App\Models\Fee;
use App\Services\CurrencyService;
use App\Services\PricingService;
use App\Services\FeeService;
use App\Services\TaxService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreateAccommodation
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 1;

    /**
     * The maximum number of seconds the job can run.
     */
    public $timeout = 120;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public $backoff = [10, 30, 60];

    protected AccommodationDraft $accommodationDraft;
    protected int $locationId;
    protected int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(AccommodationDraft $accommodationDraft, int $locationId, int $userId)
    {
        $this->accommodationDraft = $accommodationDraft;
        $this->locationId = $locationId;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(
        PricingService $pricingService,
        CurrencyService $currencyService,
        FeeService $feeService,
        TaxService $taxService,
    ): void {
        DB::beginTransaction();

        // try {
        \Log::channel('queue')->info('Creating accommodation from draft', [
            'draft_id' => $this->accommodationDraft->id,
            'location_id' => $this->locationId,
            'user_id' => $this->userId,
        ]);
        $data = json_decode($this->accommodationDraft->data, true);

        // We need to disable search syncing here to avoid indexing incomplete accommodation
        $accommodation = Accommodation::withoutSyncingToSearch(function () use ($data) {
            return $this->createAccommodation($data);
        });

        \Log::channel('queue')->info('Accommodation created', [
            'accommodation_id' => $accommodation->id,
        ]);

        if (isset($data['pricing'])) {
            $this->setupPricing($accommodation, $data, $pricingService, $currencyService);
        }

        $this->accommodationDraft->update(['status' => 'published']);

        DB::commit();

        // Notify user
        $user = User::find($this->userId);
        // Notification::send($user, new AccommodationCreatedNotification($accommodation));
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     \Log::channel('queue')->error('Error creating accommodation from draft', [
        //         'draft_id' => $this->accommodationDraft->id,
        //         'error' => $e->getMessage(),
        //     ]);
        //     throw $e;
        // }
        $accommodation->searchable();
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error('Failed to create accommodation', [
            'draft_id' => $this->accommodationDraft->id,
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
        ]);
    }

    /**
     * Create accommodation record
     */
    private function createAccommodation(array $data): Accommodation
    {
        $accommodationData = [
            'location_id' => $this->locationId,
            'accommodation_draft_id' => $this->accommodationDraft->id,
            'accommodation_type' => $data['accommodation_type'],
            'accommodation_occupation' => AccommodationOccupation::from($data['accommodation_occupation']),
            'title' => $data['title'],
            'description' => $data['description'],
            'booking_type' => $data['pricing']['bookingType'] ?? 'instant_booking',
            'amenities' => json_encode($data['amenities'] ?? []),
            'user_id' => $this->userId,
            'check_in_from' => $data['house_rules']['checkInFrom'] ?? null,
            'check_in_until' => $data['house_rules']['checkInUntil'] ?? null,
            'check_out_until' => $data['house_rules']['checkOutUntil'] ?? null,
            'quiet_hours_from' => $data['house_rules']['quietHoursFrom'] ?? null,
            'quiet_hours_until' => $data['house_rules']['quietHoursUntil'] ?? null,
            'cancellation_policy' => $data['house_rules']['cancellationPolicy'] ?? null,
            'max_guests' => $data['max_guests'] ?? 1,
            'latitude' => $data['coordinates']['latitude'] ?? null,
            'longitude' => $data['coordinates']['longitude'] ?? null,
            'approved_by' => userOrFail()->id,
            'is_active' => true,
            'is_featured' => false,

            // Add location fields for tax assignment
            'street_address' => $data['address']['street'] ?? null,
            'postal_code' => $data['address']['postal_code'] ?? null,
        ];

        return Accommodation::create($accommodationData);
    }

    private function setupPricing(Accommodation $accommodation, array $data, PricingService $pricingService, CurrencyService $currencyService): void
    {
        $pricing = $data['pricing'];

        \Log::channel('queue')->info('Setting up pricing', [
            'accommodation_id' => $accommodation->id,
            'pricing_data' => $pricing,
        ]);

        // 1. Create base pricing
        $currency = $currencyService->getCurrencyByCountry($data['address']['country'])?->code ?? 'EUR';

        $pricingData = [
            'pricing_type' => PricingType::NIGHTLY,
            'base_price' => $pricing['basePrice'],
            'currency' => $currency,
            'base_price_eur' => calculatePriceInSettedCurrency($pricing['basePrice'], $currency, 'EUR'),
            'min_quantity' => $pricing['minNights'] ?? 1,
            'max_quantity' => $pricing['maxNights'] ?? null,
            'is_active' => true,
        ];

        // Weekend pricing
        // if (isset($pricing['weekendPricing']) && $pricing['weekendPricing']['enabled']) {
        //     $pricingData['has_weekend_pricing'] = true;
        //     $pricingData['weekend_price'] = $pricing['weekendPricing']['price'];
        //     $pricingData['weekend_days'] = $pricing['weekendPricing']['days'] ?? ['friday', 'saturday'];
        // }

        // Bulk discount (weekly/monthly)
        // if (isset($pricing['bulkDiscount']) && $pricing['bulkDiscount']['enabled']) {
        //     $pricingData['bulk_discount_percent'] = $pricing['bulkDiscount']['percent'];
        //     $pricingData['bulk_discount_threshold'] = $pricing['bulkDiscount']['threshold'];
        // }

        // Day-specific minimum stay (e.g., Friday check-in requires 3 nights)
        // if (isset($pricing['daySpecificMinStay'])) {
        //     $pricingData['time_constraints'] = [
        //         'day_specific_min_stay' => $pricing['daySpecificMinStay']
        //     ];
        // }

        $priceableItem = $pricingService->createPricing(
            Accommodation::class,
            $accommodation->id,
            $pricingData
        );

        \Log::channel('queue')->info('Base pricing created', [
            'priceable_item_id' => $priceableItem->id,
        ]);

        // 2. Add seasonal pricing periods
        // if (isset($pricing['seasonalPricing']) && is_array($pricing['seasonalPricing'])) {
        //     foreach ($pricing['seasonalPricing'] as $season) {
        //         $pricingService->addPricingPeriod(
        //             Accommodation::class,
        //             $accommodation->id,
        //             [
        //                 'period_type' => 'seasonal',
        //                 'name' => $season['name'],
        //                 'start_date' => $season['startDate'],
        //                 'end_date' => $season['endDate'],
        //                 'price_override' => $season['price'] ?? null,
        //                 'price_multiplier' => $season['priceMultiplier'] ?? null,
        //                 'priority' => 1,
        //                 'is_active' => true,
        //             ]
        //         );

        //         \Log::channel('queue')->info('Seasonal pricing added', [
        //             'season_name' => $season['name'],
        //         ]);
        //     }
        // }

        // 3. Add special date pricing (Christmas, New Year, etc.)
        // if (isset($pricing['specialDates']) && is_array($pricing['specialDates'])) {
        //     foreach ($pricing['specialDates'] as $specialDate) {
        //         $pricingService->addPricingPeriod(
        //             Accommodation::class,
        //             $accommodation->id,
        //             [
        //                 'period_type' => 'special_date',
        //                 'name' => $specialDate['name'],
        //                 'start_date' => $specialDate['startDate'],
        //                 'end_date' => $specialDate['endDate'],
        //                 'price_override' => $specialDate['price'] ?? null,
        //                 'price_multiplier' => $specialDate['priceMultiplier'] ?? null,
        //                 'min_quantity_override' => $specialDate['minNights'] ?? null,
        //                 'priority' => 2, // Higher priority than seasonal
        //                 'is_active' => true,
        //             ]
        //         );

        //         \Log::channel('queue')->info('Special date pricing added', [
        //             'date_name' => $specialDate['name'],
        //         ]);
        //     }
        // }

        // if (isset($pricing['standardFees'])) {
        //     $this->setupFees($accommodation, $pricing['standardFees'], $feeService);
        // }

        // if (isset($pricing['amenityFees'])) {
        //     $this->setupFees($accommodation, $pricing['amenityFees'], $feeService);
        // }

        // if (isset($pricing['customFees'])) {
        //     $this->setupFees($accommodation, $pricing['customFees'], $feeService);
        // }

        // $this->setupTaxes($accommodation, $pricing, $taxService);
    }

    /**
     * Setup fees
     *
     * Expected $fees structure:
     * [
     *   {
     *     "type": "cleaning",
     *     "name": "Cleaning Fee",
     *     "amount": 30.00,
     *     "chargeType": "per_booking",
     *     "mandatory": true
     *   },
     *   {
     *     "type": "extra_guest",
     *     "name": "Extra Guest Fee",
     *     "amount": 10.00,
     *     "chargeType": "per_person_per_unit",
     *     "appliesAfterPersons": 2,
     *     "mandatory": true
     *   },
     *   {
     *     "type": "pet",
     *     "name": "Pet Fee",
     *     "amount": 20.00,
     *     "chargeType": "per_booking",
     *     "mandatory": false
     *   }
     * ]
     */
    private function setupFees(Accommodation $accommodation, array $fees, FeeService $feeService): void
    {
        \Log::channel('queue')->info('Setting up fees', [
            'accommodation_id' => $accommodation->id,
            'fees_count' => count($fees),
        ]);

        foreach ($fees as $fee) {
            $feeData = [
                'fee_type' => $fee['feeType'],
                'name' => $fee['name'] ?? null,
                'description' => $fee['description'] ?? null,
                'amount' => $fee['amount'],
                'currency' => $fee['currency'] ?? 'EUR',
                'charge_type' => $fee['chargeType'] ?? 'per_booking',
                'mandatory' => $fee['mandatory'] ?? true,
                'is_taxable' => $fee['isTaxable'] ?? true,
                'is_refundable' => $fee['isRefundable'] ?? false,
                'show_in_breakdown' => true,
                'is_active' => true,
            ];

            // Percentage fees (e.g., service charge)
            if (isset($fee['percentageRate'])) {
                $feeData['percentage_rate'] = $fee['percentageRate'];
                $feeData['percentage_basis'] = $fee['percentageBasis'] ?? 'subtotal';
            }

            // Conditional application
            if (isset($fee['appliesAfterQuantity'])) {
                $feeData['applies_after_quantity'] = $fee['appliesAfterQuantity'];
            }
            if (isset($fee['appliesAfterPersons'])) {
                $feeData['applies_after_persons'] = $fee['appliesAfterPersons'];
            }
            if (isset($fee['appliesAfterAmount'])) {
                $feeData['applies_after_amount'] = $fee['appliesAfterAmount'];
            }

            // Refund configuration
            if (isset($fee['refundPercentage'])) {
                $feeData['refund_percentage'] = $fee['refundPercentage'];
            }
            if (isset($fee['refundDays'])) {
                $feeData['refund_days'] = $fee['refundDays'];
            }

            $createdFee = $feeService->createFee(
                Accommodation::class,
                $accommodation->id,
                $feeData
            );

            \Log::channel('queue')->info('Fee created', [
                'fee_id' => $createdFee->id,
                'fee_type' => $fee['feeType'],
            ]);
        }
    }

    /**
     * Setup taxes (auto-assign based on location)
     */
    private function setupTaxes(Accommodation $accommodation, array $data, TaxService $taxService): void
    {
        \Log::channel('queue')->info('Setting up taxes', [
            'accommodation_id' => $accommodation->id,
        ]);

        // Auto-assign taxes based on accommodation location
        $countryCode = $data['location']['country_code'] ?? $accommodation->country_code ?? 'RS';
        $regionCode = $data['location']['region_code'] ?? $accommodation->region_code ?? null;
        $city = $data['location']['city'] ?? $accommodation->city ?? null;

        $assignedTaxes = $taxService->assignTaxesByLocation(
            Accommodation::class,
            $accommodation->id,
            $countryCode,
            $regionCode,
            $city
        );

        \Log::channel('queue')->info('Taxes assigned', [
            'taxes_count' => count($assignedTaxes),
            'country' => $countryCode,
        ]);

        // Handle manual tax exemptions if provided
        if (isset($data['taxExemptions']) && is_array($data['taxExemptions'])) {
            foreach ($data['taxExemptions'] as $exemption) {
                if (isset($exemption['entityTaxId'])) {
                    $taxService->setTaxExemption(
                        $exemption['entityTaxId'],
                        $exemption['reason'],
                        $exemption['certificateNumber'] ?? null,
                        isset($exemption['validUntil']) ? Carbon::parse($exemption['validUntil']) : null
                    );

                    \Log::channel('queue')->info('Tax exemption set', [
                        'entity_tax_id' => $exemption['entityTaxId'],
                    ]);
                }
            }
        }
    }
}
