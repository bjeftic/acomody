<?php

namespace App\Jobs;

use App\Enums\Accommodation\AccommodationOccupation;
use App\Enums\PriceableItem\PricingType;
use App\Mail\Accommodation\AccommodationApprovedMail;
use App\Models\Accommodation;
use App\Models\AccommodationDraft;
use App\Models\Currency;
use App\Models\Photo;
use App\Services\CurrencyService;
use App\Services\FeeService;
use App\Services\PricingService;
use App\Services\TaxService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class CreateAccommodation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 1;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 120;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array<int>
     */
    public array $backoff = [10, 30, 60];

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $accommodationDraftId,
        protected string $locationId,
        protected int $userId,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        PricingService $pricingService,
        CurrencyService $currencyService,
        FeeService $feeService,
        TaxService $taxService,
    ): void {
        // Authenticate as the superadmin who approved the draft so that
        // Authorizable checks pass throughout the job execution.
        Auth::loginUsingId($this->userId);

        $draft = AccommodationDraft::with('user')->findOrFail($this->accommodationDraftId);

        DB::beginTransaction();

        try {
            Log::channel('queue')->info('Creating accommodation from draft', [
                'draft_id' => $draft->id,
                'location_id' => $this->locationId,
                'user_id' => $this->userId,
            ]);

            $data = json_decode($draft->data, true);

            $accommodation = Accommodation::withoutSyncingToSearch(function () use ($data, $draft) {
                return $this->createAccommodation($data, $draft);
            });

            Log::channel('queue')->info('Accommodation created', [
                'accommodation_id' => $accommodation->id,
            ]);

            if (isset($data['pricing'])) {
                $this->setupPricing($accommodation, $data, $pricingService, $currencyService);
            }

            $this->transferPhotos($accommodation, $draft);

            $draft->update(['status' => 'published']);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::channel('queue')->error('Error creating accommodation from draft', [
                'draft_id' => $draft->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }

        $accommodation->searchable();

        if ($draft->user) {
            Mail::to($draft->user->email)
                ->queue(new AccommodationApprovedMail($draft));
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Failed to create accommodation', [
            'draft_id' => $this->accommodationDraftId,
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
        ]);
    }

    /**
     * Transfer photos from the accommodation draft to the newly created accommodation,
     * copying files across disks and removing the originals from the draft.
     */
    private function transferPhotos(Accommodation $accommodation, AccommodationDraft $draft): void
    {
        $draftDisk = config('images.presets.accommodation_draft.disk', 'accommodation_draft_photos');
        $accommodationDisk = config('images.presets.accommodation.disk', 'accommodation_photos');

        $photos = $draft->photos()->get();

        foreach ($photos as $photo) {
            $newPaths = $this->copyPhotoFiles($photo, $draftDisk, $accommodationDisk, $accommodation->id);

            Photo::create([
                'photoable_type' => Accommodation::class,
                'photoable_id' => $accommodation->id,
                'disk' => $accommodationDisk,
                'path' => $newPaths['path'],
                'thumbnail_path' => $newPaths['thumbnail_path'],
                'medium_path' => $newPaths['medium_path'],
                'large_path' => $newPaths['large_path'],
                'original_filename' => $photo->original_filename,
                'mime_type' => $photo->mime_type,
                'file_size' => $photo->file_size,
                'width' => $photo->width,
                'height' => $photo->height,
                'order' => $photo->order,
                'is_primary' => $photo->is_primary,
                'status' => $photo->status,
                'alt_text' => $photo->alt_text,
                'caption' => $photo->caption,
                'metadata' => $photo->metadata,
                'uploaded_at' => $photo->uploaded_at,
                'processed_at' => $photo->processed_at,
            ]);
        }

        // Delete draft photo records — the Photo booted() hook also removes their files from the draft disk.
        $draft->photos()->each(fn (Photo $p) => $p->delete());

        Log::channel('queue')->info('Photos transferred from draft to accommodation', [
            'draft_id' => $draft->id,
            'accommodation_id' => $accommodation->id,
            'count' => $photos->count(),
        ]);
    }

    /**
     * Copy all size variants of a photo from the draft disk to the accommodation disk.
     *
     * @return array<string, string|null>
     */
    private function copyPhotoFiles(Photo $photo, string $fromDisk, string $toDisk, string $accommodationId): array
    {
        $pathFields = ['path', 'thumbnail_path', 'medium_path', 'large_path'];
        $newPaths = [];

        foreach ($pathFields as $field) {
            $originalPath = $photo->$field;

            if (empty($originalPath)) {
                $newPaths[$field] = null;

                continue;
            }

            // Replace "draft-{id}" prefix with "property-{id}"
            $newPath = preg_replace(
                '#^draft-[^/]+/#',
                "property-{$accommodationId}/",
                $originalPath
            );

            $fromStorage = Storage::disk($fromDisk);
            $toStorage = Storage::disk($toDisk);

            if ($fromStorage->exists($originalPath)) {
                $toStorage->put($newPath, $fromStorage->get($originalPath));
            }

            $newPaths[$field] = $newPath;
        }

        return $newPaths;
    }

    /**
     * Create accommodation record.
     */
    private function createAccommodation(array $data, AccommodationDraft $draft): Accommodation
    {
        $accommodation = Accommodation::create([
            'location_id' => $this->locationId,
            'accommodation_draft_id' => $draft->id,
            'accommodation_type' => $data['accommodation_type'],
            'accommodation_occupation' => AccommodationOccupation::from($data['accommodation_occupation']),
            'title' => $data['title'],
            'description' => $data['description'],
            'booking_type' => $data['pricing']['bookingType'] ?? 'instant_booking',
            'user_id' => $draft->user_id,
            'check_in_from' => $data['house_rules']['checkInFrom'] ?? null,
            'check_in_until' => $data['house_rules']['checkInUntil'] ?? null,
            'check_out_until' => $data['house_rules']['checkOutUntil'] ?? null,
            'quiet_hours_from' => $data['house_rules']['quietHoursFrom'] ?? null,
            'quiet_hours_until' => $data['house_rules']['quietHoursUntil'] ?? null,
            'cancellation_policy' => $data['house_rules']['cancellationPolicy'] ?? null,
            'max_guests' => $data['floor_plan']['guests'] ?? 1,
            'bedrooms' => $data['floor_plan']['bedrooms'] ?? 1,
            'bathrooms' => $data['floor_plan']['bathrooms'] ?? 1,
            'latitude' => $data['coordinates']['latitude'] ?? null,
            'longitude' => $data['coordinates']['longitude'] ?? null,
            'approved_by' => $this->userId,
            'is_active' => true,
            'is_featured' => false,
            'street_address' => $data['address']['street'] ?? null,
        ]);

        if (! empty($data['amenities'])) {
            $accommodation->amenities()->sync($data['amenities']);
        }

        $bedTypes = collect($data['floor_plan']['bed_types'] ?? [])
            ->filter(fn (array $bt) => ($bt['quantity'] ?? 0) > 0)
            ->map(fn (array $bt) => [
                'bed_type' => $bt['bed_type'],
                'quantity' => $bt['quantity'],
            ])
            ->values()
            ->toArray();

        if (! empty($bedTypes)) {
            $accommodation->beds()->createMany($bedTypes);
        }

        return $accommodation;
    }

    private function setupPricing(Accommodation $accommodation, array $data, PricingService $pricingService, CurrencyService $currencyService): void
    {
        $pricing = $data['pricing'];

        Log::channel('queue')->info('Setting up pricing', [
            'accommodation_id' => $accommodation->id,
            'pricing_data' => $pricing,
        ]);

        $currency = $currencyService->getCurrencyByCountry($data['address']['country']) ?? Currency::where('code', 'EUR')->first();

        $pricingData = [
            'pricing_type' => PricingType::NIGHTLY,
            'base_price' => $pricing['basePrice'],
            'currency_id' => $currency->id,
            'base_price_eur' => calculatePriceInSettedCurrency($pricing['basePrice'], $currency->code, 'EUR'),
            'min_quantity' => $pricing['minNights'] ?? 1,
            'max_quantity' => $pricing['maxNights'] ?? null,
            'is_active' => true,
        ];

        $priceableItem = $pricingService->createPricing(
            Accommodation::class,
            $accommodation->id,
            $pricingData
        );

        Log::channel('queue')->info('Base pricing created', [
            'priceable_item_id' => $priceableItem->id,
        ]);

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

        // Seasonal pricing periods
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
        //
        //         Log::channel('queue')->info('Seasonal pricing added', [
        //             'season_name' => $season['name'],
        //         ]);
        //     }
        // }

        // Special date pricing (Christmas, New Year, etc.)
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
        //                 'priority' => 2,
        //                 'is_active' => true,
        //             ]
        //         );
        //
        //         Log::channel('queue')->info('Special date pricing added', [
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

        // $this->setupTaxes($accommodation, $data, $taxService);
    }

    /**
     * Setup fees.
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
     *   ...
     * ]
     */
    // private function setupFees(Accommodation $accommodation, array $fees, FeeService $feeService): void
    // {
    //     Log::channel('queue')->info('Setting up fees', [
    //         'accommodation_id' => $accommodation->id,
    //         'fees_count' => count($fees),
    //     ]);
    //
    //     foreach ($fees as $fee) {
    //         $feeData = [
    //             'fee_type' => $fee['feeType'],
    //             'name' => $fee['name'] ?? null,
    //             'description' => $fee['description'] ?? null,
    //             'amount' => $fee['amount'],
    //             'currency' => $fee['currency'] ?? 'EUR',
    //             'charge_type' => $fee['chargeType'] ?? 'per_booking',
    //             'mandatory' => $fee['mandatory'] ?? true,
    //             'is_taxable' => $fee['isTaxable'] ?? true,
    //             'is_refundable' => $fee['isRefundable'] ?? false,
    //             'show_in_breakdown' => true,
    //             'is_active' => true,
    //         ];
    //
    //         if (isset($fee['percentageRate'])) {
    //             $feeData['percentage_rate'] = $fee['percentageRate'];
    //             $feeData['percentage_basis'] = $fee['percentageBasis'] ?? 'subtotal';
    //         }
    //
    //         if (isset($fee['appliesAfterQuantity'])) {
    //             $feeData['applies_after_quantity'] = $fee['appliesAfterQuantity'];
    //         }
    //         if (isset($fee['appliesAfterPersons'])) {
    //             $feeData['applies_after_persons'] = $fee['appliesAfterPersons'];
    //         }
    //         if (isset($fee['appliesAfterAmount'])) {
    //             $feeData['applies_after_amount'] = $fee['appliesAfterAmount'];
    //         }
    //
    //         if (isset($fee['refundPercentage'])) {
    //             $feeData['refund_percentage'] = $fee['refundPercentage'];
    //         }
    //         if (isset($fee['refundDays'])) {
    //             $feeData['refund_days'] = $fee['refundDays'];
    //         }
    //
    //         $createdFee = $feeService->createFee(
    //             Accommodation::class,
    //             $accommodation->id,
    //             $feeData
    //         );
    //
    //         Log::channel('queue')->info('Fee created', [
    //             'fee_id' => $createdFee->id,
    //             'fee_type' => $fee['feeType'],
    //         ]);
    //     }
    // }

    /**
     * Setup taxes (auto-assign based on location).
     */
    // private function setupTaxes(Accommodation $accommodation, array $data, TaxService $taxService): void
    // {
    //     Log::channel('queue')->info('Setting up taxes', [
    //         'accommodation_id' => $accommodation->id,
    //     ]);
    //
    //     $countryCode = $data['location']['country_code'] ?? $accommodation->country_code ?? 'RS';
    //     $regionCode = $data['location']['region_code'] ?? $accommodation->region_code ?? null;
    //     $city = $data['location']['city'] ?? $accommodation->city ?? null;
    //
    //     $assignedTaxes = $taxService->assignTaxesByLocation(
    //         Accommodation::class,
    //         $accommodation->id,
    //         $countryCode,
    //         $regionCode,
    //         $city
    //     );
    //
    //     Log::channel('queue')->info('Taxes assigned', [
    //         'taxes_count' => count($assignedTaxes),
    //         'country' => $countryCode,
    //     ]);
    //
    //     if (isset($data['taxExemptions']) && is_array($data['taxExemptions'])) {
    //         foreach ($data['taxExemptions'] as $exemption) {
    //             if (isset($exemption['entityTaxId'])) {
    //                 $taxService->setTaxExemption(
    //                     $exemption['entityTaxId'],
    //                     $exemption['reason'],
    //                     $exemption['certificateNumber'] ?? null,
    //                     isset($exemption['validUntil']) ? Carbon::parse($exemption['validUntil']) : null
    //                 );
    //
    //                 Log::channel('queue')->info('Tax exemption set', [
    //                     'entity_tax_id' => $exemption['entityTaxId'],
    //                 ]);
    //             }
    //         }
    //     }
    // }
}
