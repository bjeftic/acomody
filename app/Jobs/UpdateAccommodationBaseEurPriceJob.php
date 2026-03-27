<?php

namespace App\Jobs;

use App\Models\Accommodation;
use App\Models\ExchangeRate;
use App\Models\PriceableItem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class UpdateAccommodationBaseEurPriceJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(public readonly string $priceableItemId) {}

    public function handle(): void
    {
        $item = PriceableItem::withoutAuthorization(
            fn () => PriceableItem::query()->with('currency')->find($this->priceableItemId)
        );

        if (! $item || ! $item->currency) {
            return;
        }

        if ($item->currency->code === 'EUR') {
            return;
        }

        $rate = ExchangeRate::withoutAuthorization(
            fn () => ExchangeRate::getLatestRate('EUR', $item->currency->code)
        );

        if (! $rate) {
            Log::warning("UpdateAccommodationBaseEurPriceJob: no EUR→{$item->currency->code} rate found for PriceableItem {$this->priceableItemId}");

            return;
        }

        $basePriceEur = round((float) $item->base_price / $rate, 2);

        PriceableItem::withoutAuthorization(function () use ($item, $basePriceEur) {
            $item->update(['base_price_eur' => $basePriceEur]);
        });

        // Re-index the accommodation in Typesense so base_price_eur stays current
        if ($item->priceable_type === Accommodation::class) {
            $accommodation = Accommodation::withoutAuthorization(
                fn () => Accommodation::query()->find($item->priceable_id)
            );

            if ($accommodation?->isSearchable()) {
                $accommodation->searchable();
            }
        }
    }
}
