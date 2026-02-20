<?php

namespace App\Console\Commands;

use App\Models\Currency;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchCurrencyRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:fetch-rates';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch latest currency exchange rates from external API.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Fetching latest currency exchange rates...");

        $apiKey = config('services.exchangerate.api_key');
        if (empty($apiKey)) {
            $this->error("Exchange rate API key is not configured.");
            return 1;
        }

        $url = "https://v6.exchangerate-api.com/v6/{$apiKey}/latest/EUR";

        try {
            $response = file_get_contents($url);

            if ($response === false) {
                throw new \Exception("Failed to fetch exchange rates.");
            }

            $data = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Invalid JSON response from exchange rate API.");
            }

            if ($data['result'] !== 'success') {
                throw new \Exception("Exchange rate API error: " . ($data['error-type'] ?? 'Unknown error'));
            }

            $rates = $data['conversion_rates'];
        } catch (\Exception $e) {
            Log::error("FetchCurrencyRates error: " . $e->getMessage());
            $this->error("Failed to fetch currency rates: " . $e->getMessage());
            return 1;
        }

        if (empty($rates)) {
            $this->error("No rates returned from API.");
            return 1;
        }

        $this->info("Successfully fetched currency rates. Processing...");

        Currency::withoutAuthorization(function () use ($rates) {
            foreach ($rates as $code => $rate) {
                if ($code === 'EUR') {
                    continue;
                }

                $currency = Currency::where('code', $code)->where('is_active', true)->first();
                if (!$currency) {
                    continue;
                }

                // Deactivate previous rate
                $currency->exchangeRates()
                    ->where('is_active', true)
                    ->update(['is_active' => false]);

                // Create new rate
                $currency->exchangeRates()->create([
                    'from_currency' => 'EUR',
                    'to_currency'   => $currency->code,
                    'rate'          => $rate,
                    'date'          => now()->toDateString(),
                    'source'        => 'exchangerate-api',
                    'is_active'     => true,
                ]);

                $this->line("1 EUR = {$rate} {$currency->code}");
            }
        });

        $this->info("Currency rates updated successfully.");
        return 0;
    }
}
