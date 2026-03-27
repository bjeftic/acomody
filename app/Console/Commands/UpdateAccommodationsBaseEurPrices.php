<?php

namespace App\Console\Commands;

use App\Jobs\UpdateAccommodationBaseEurPriceJob;
use App\Models\PriceableItem;
use Illuminate\Console\Command;

class UpdateAccommodationsBaseEurPrices extends Command
{
    protected $signature = 'app:update-accommodations-base-eur-prices
                            {--skip-rate-fetch : Skip fetching latest rates (for testing)}';

    protected $description = 'Fetch latest exchange rates then update base_price_eur for all non-EUR accommodation prices.';

    public function handle(): int
    {
        if (! $this->option('skip-rate-fetch')) {
            $this->info('Fetching latest currency exchange rates...');

            $exitCode = $this->call('currency:fetch-rates');

            if ($exitCode !== 0) {
                $this->error('Failed to fetch currency rates. Aborting EUR price update.');

                return 1;
            }
        }

        $this->info('Dispatching EUR price update jobs for non-EUR accommodations...');

        $count = 0;

        PriceableItem::withoutAuthorization(function () use (&$count) {
            PriceableItem::query()
                ->whereHas('currency', fn ($q) => $q->where('code', '!=', 'EUR'))
                ->where('priceable_type', \App\Models\Accommodation::class)
                ->where('is_active', true)
                ->select('id')
                ->chunk(100, function ($items) use (&$count) {
                    foreach ($items as $item) {
                        dispatch(new UpdateAccommodationBaseEurPriceJob($item->id));
                        $count++;
                    }
                });
        });

        $this->info("Dispatched {$count} EUR price update job(s).");

        return 0;
    }
}
