<?php

use App\Enums\PriceableItem\PricingType;
use App\Jobs\UpdateAccommodationBaseEurPriceJob;
use App\Models\Accommodation;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\PriceableItem;
use Illuminate\Support\Facades\Queue;

beforeEach(fn () => seedCurrencyRates());

// ============================================================
// UpdateAccommodationBaseEurPriceJob
// ============================================================

describe('UpdateAccommodationBaseEurPriceJob', function () {

    it('updates base_price_eur using the latest exchange rate for a non-EUR accommodation', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        $rsdCurrency = Currency::where('code', 'RSD')->first();

        PriceableItem::withoutAuthorization(function () use ($accommodation, $rsdCurrency) {
            $accommodation->pricing()->delete();

            PriceableItem::create([
                'priceable_type' => Accommodation::class,
                'priceable_id' => $accommodation->id,
                'pricing_type' => PricingType::NIGHTLY,
                'base_price' => 11700.00,
                'currency_id' => $rsdCurrency->id,
                'base_price_eur' => null,
                'min_quantity' => 1,
                'is_active' => true,
            ]);
        });

        $item = PriceableItem::withoutAuthorization(
            fn () => PriceableItem::where('priceable_id', $accommodation->id)->first()
        );

        UpdateAccommodationBaseEurPriceJob::dispatchSync($item->id);

        $item->refresh();

        // EUR→RSD rate is 117.0 (seeded in seedCurrencyRates), so 11700 / 117 = 100
        expect((float) $item->base_price_eur)->toEqual(100.0);
    });

    it('skips update when the accommodation currency is already EUR', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        $eurCurrency = Currency::where('code', 'EUR')->first();

        PriceableItem::withoutAuthorization(function () use ($accommodation, $eurCurrency) {
            $accommodation->pricing()->delete();

            PriceableItem::create([
                'priceable_type' => Accommodation::class,
                'priceable_id' => $accommodation->id,
                'pricing_type' => PricingType::NIGHTLY,
                'base_price' => 80.00,
                'currency_id' => $eurCurrency->id,
                'base_price_eur' => 999.00,
                'min_quantity' => 1,
                'is_active' => true,
            ]);
        });

        $item = PriceableItem::withoutAuthorization(
            fn () => PriceableItem::where('priceable_id', $accommodation->id)->first()
        );

        UpdateAccommodationBaseEurPriceJob::dispatchSync($item->id);

        $item->refresh();

        // EUR items are skipped — value should remain unchanged
        expect((float) $item->base_price_eur)->toEqual(999.00);
    });

    it('does nothing when the priceable item does not exist', function () {
        // Should not throw
        UpdateAccommodationBaseEurPriceJob::dispatchSync('non-existent-id');

        expect(true)->toBeTrue();
    });

    it('leaves base_price_eur unchanged when no exchange rate is found', function () {
        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        ExchangeRate::withoutAuthorization(fn () => ExchangeRate::query()->delete());

        $usdCurrency = Currency::where('code', 'USD')->first();

        PriceableItem::withoutAuthorization(function () use ($accommodation, $usdCurrency) {
            $accommodation->pricing()->delete();

            PriceableItem::create([
                'priceable_type' => Accommodation::class,
                'priceable_id' => $accommodation->id,
                'pricing_type' => PricingType::NIGHTLY,
                'base_price' => 100.00,
                'currency_id' => $usdCurrency->id,
                'base_price_eur' => 92.00,
                'min_quantity' => 1,
                'is_active' => true,
            ]);
        });

        $item = PriceableItem::withoutAuthorization(
            fn () => PriceableItem::where('priceable_id', $accommodation->id)->first()
        );

        // ExchangeRate::getLatestRate throws when table is empty — job should handle gracefully
        try {
            UpdateAccommodationBaseEurPriceJob::dispatchSync($item->id);
        } catch (\Exception) {
            // Expected — empty rates table throws
        }

        $item->refresh();

        expect((float) $item->base_price_eur)->toEqual(92.00);
    });
});

// ============================================================
// UpdateAccommodationsBaseEurPrices command — job dispatching
// ============================================================

describe('UpdateAccommodationsBaseEurPrices command', function () {

    it('dispatches a job for each active non-EUR accommodation', function () {
        Queue::fake();

        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        $rsdCurrency = Currency::where('code', 'RSD')->first();

        PriceableItem::withoutAuthorization(function () use ($accommodation, $rsdCurrency) {
            $accommodation->pricing()->delete();

            PriceableItem::create([
                'priceable_type' => Accommodation::class,
                'priceable_id' => $accommodation->id,
                'pricing_type' => PricingType::NIGHTLY,
                'base_price' => 11700.00,
                'currency_id' => $rsdCurrency->id,
                'base_price_eur' => null,
                'min_quantity' => 1,
                'is_active' => true,
            ]);
        });

        $this->artisan('app:update-accommodations-base-eur-prices', ['--skip-rate-fetch' => true])
            ->assertExitCode(0);

        Queue::assertPushed(UpdateAccommodationBaseEurPriceJob::class);
    });

    it('does not dispatch jobs for EUR accommodations', function () {
        Queue::fake();

        $user = authenticatedUser();
        $accommodation = createAccommodation($user);

        $eurCurrency = Currency::where('code', 'EUR')->first();

        PriceableItem::withoutAuthorization(function () use ($accommodation, $eurCurrency) {
            $accommodation->pricing()->delete();

            PriceableItem::create([
                'priceable_type' => Accommodation::class,
                'priceable_id' => $accommodation->id,
                'pricing_type' => PricingType::NIGHTLY,
                'base_price' => 80.00,
                'currency_id' => $eurCurrency->id,
                'base_price_eur' => 80.00,
                'min_quantity' => 1,
                'is_active' => true,
            ]);
        });

        $this->artisan('app:update-accommodations-base-eur-prices', ['--skip-rate-fetch' => true])
            ->assertExitCode(0);

        Queue::assertNothingPushed();
    });

    it('aborts with exit code 1 when currency fetch has no api key configured', function () {
        Queue::fake();

        // Ensure no API key is set so currency:fetch-rates returns 1
        config(['services.exchangerate.api_key' => null]);

        $this->artisan('app:update-accommodations-base-eur-prices')->assertExitCode(1);

        Queue::assertNothingPushed();
    });
});
