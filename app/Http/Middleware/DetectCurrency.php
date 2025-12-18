<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\CurrencyService;
use Stevebauman\Location\Facades\Location;

class DetectCurrency
{
    private CurrencyService $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $hasSessionCurrency = $request->session()->has('currency');
        $hasCookieCurrency = $request->hasCookie('currency');
        $hasUserCurrency = $user && $user->preferred_currency;

        // If no currency is set anywhere - this is first visit
        if (!$hasSessionCurrency && !$hasCookieCurrency && !$hasUserCurrency) {
            $this->handleFirstVisit($request, $user);
        }
        // If user just logged in and has no preference, use session/cookie
        elseif ($user && !$hasUserCurrency && ($hasSessionCurrency || $hasCookieCurrency)) {
            $this->syncCurrencyToUser($request, $user);
        }
        // If user just logged in and has preference different from session
        elseif ($user && $hasUserCurrency) {
            $this->syncUserCurrencyToSession($request, $user);
        }

        return $next($request);
    }

    /**
     * Handle first visit - detect and set currency
     */
    private function handleFirstVisit(Request $request, $user): void
    {
        try {
            $position = Location::get($request->ip());

            $countryCode = $position?->countryCode;

            $detectedCurrency = CurrencyService::detectCurrency($countryCode);

            $request->session()->put('currency', $detectedCurrency);
            $request->session()->put('currency_auto_detected', true);
            $request->session()->put('currency_detected_at', now()->toISOString());

            if ($user && !$user->preferred_currency) {
                $user->update([
                    'preferred_currency' => $detectedCurrency,
                    'detected_currency'  => $detectedCurrency,
                    'detected_country'   => $countryCode,
                ]);
            }
        } catch (\Throwable $e) {
            \Log::error('Failed to detect currency on first visit', [
                'error' => $e->getMessage(),
                'ip'    => $request->ip(),
            ]);

            $defaultCurrency = config('currency.default', 'EUR');
            $request->session()->put('currency', $defaultCurrency);
        }
    }

    /**
     * User logged in - sync session currency to user if they don't have preference
     */
    private function syncCurrencyToUser(Request $request, $user): void
    {
        $sessionCurrency = $request->session()->get('currency')
            ?? $request->cookie('currency');

        if ($sessionCurrency) {
            $user->update(['preferred_currency' => $sessionCurrency]);
        }
    }

    /**
     * User logged in with preference - sync to session
     */
    private function syncUserCurrencyToSession(Request $request, $user): void
    {
        $userCurrency = $user->preferred_currency;
        $sessionCurrency = $request->session()->get('currency');

        // If different, use user's preference
        if ($userCurrency && $userCurrency !== $sessionCurrency) {
            $request->session()->put('currency', $userCurrency);
        }
    }
}
