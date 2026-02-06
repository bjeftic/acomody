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

        $rates = [];

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
            Log::error("ExchangeService fetchRates error: " . $e->getMessage());
            return [];
        }

        if (empty($rates)) {
            $this->error("Failed to fetch currency rates.");
            return 1;
        }
        // Here you would typically store the rates in the database or cache
        $this->info("Successfully fetched currency rates.");
        foreach ($rates as $currency => $rate) {
            if($currency === 'EUR') {
                continue; // Skip base currency
            }

            $currency = Currency::where('code', $currency)->first();
            if (!$currency) {
                continue;
            }

            if (!$currency->is_active) {
                continue;
            }

            $lastRate = $currency->exchangeRates()->orderByDesc('date')->first();

            $currency->exchangeRates()->create([
                'from_currency' => 'EUR',
                'to_currency' => $currency->code,
                'rate' => $rate,
                'date' => now()->format('Y-m-d'),
                'source' => 'exchangerate-api',
                'is_active' => true,
            ]);

            if($lastRate) {
                $lastRate->is_active = false;
                $lastRate->save();
            }

            $this->line("1 EUR = {$rate} {$currency->code}");
        }
        return 0;
    }
}
