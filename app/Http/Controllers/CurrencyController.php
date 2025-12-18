<?php

namespace App\Http\Controllers;

use App\Http\Requests\Currency\SetRequest;
use App\Http\Resources\CurrencyResource;
use App\Http\Support\ApiResponse;
use App\Services\CurrencyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class CurrencyController extends Controller
{
    private CurrencyService $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * Set user's preferred currency (manual change)
     */
    public function set(SetRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $user = $request->user();
            $previousCurrency = CurrencyService::getUserCurrency();

            $success = $this->currencyService->setUserCurrency(
                $user,
                $validated['currency'],
                $request
            );

            if (!$success) {
                throw ValidationException::withMessages([
                    'currency' => ['Invalid currency code.']
                ]);
            }

            $currencyData = $this->currencyService->getCurrency($validated['currency']);

            Log::info('User manually changed currency', [
                'user_id' => $user?->id,
                'from' => $previousCurrency,
                'to' => $validated['currency'],
                'ip' => $request->ip()
            ]);

            // Set cookie for 1 year (persistent across sessions)
            return ApiResponse::success(
                'Currency updated successfully.',
                new CurrencyResource($currencyData),
                [
                    'previous_currency' => $previousCurrency,
                    'was_manually_set' => true
                ]
                );
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to set currency', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return ApiResponse::error(
                'Failed to update currency.',
                null,
                null,
                500
            );
        }
    }
}
