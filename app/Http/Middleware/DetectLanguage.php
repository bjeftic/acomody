<?php

namespace App\Http\Middleware;

use App\Services\LanguageService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DetectLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasSession()) {
            return $next($request);
        }

        $user = $request->user();
        $hasSessionLanguage = $request->session()->has('language');
        $hasUserLanguage = $user && $user->preferred_language && LanguageService::isSupported($user->preferred_language);

        if (! $hasSessionLanguage && ! $hasUserLanguage) {
            $this->handleFirstVisit($request, $user);
        } elseif ($user && ! $hasUserLanguage && $hasSessionLanguage) {
            $this->syncLanguageToUser($request, $user);
        } elseif ($user && $hasUserLanguage) {
            $this->syncUserLanguageToSession($request, $user);
        }

        $language = LanguageService::getUserLanguage();
        LanguageService::applyLocale($language);

        return $next($request);
    }

    private function handleFirstVisit(Request $request, mixed $user): void
    {
        try {
            $position = \Stevebauman\Location\Facades\Location::get($request->ip());
            $detected = LanguageService::detectLanguage($position?->countryCode);
        } catch (\Throwable) {
            $detected = config('language.default', 'en');
        }

        $request->session()->put('language', $detected);
        $request->session()->put('language_auto_detected', true);

        if ($user && ! $user->preferred_language) {
            $user->update(['preferred_language' => $detected]);
        }
    }

    private function syncLanguageToUser(Request $request, mixed $user): void
    {
        $sessionLanguage = $request->session()->get('language');
        if ($sessionLanguage && LanguageService::isSupported($sessionLanguage)) {
            $user->update(['preferred_language' => $sessionLanguage]);
        }
    }

    private function syncUserLanguageToSession(Request $request, mixed $user): void
    {
        $userLanguage = $user->preferred_language;
        $sessionLanguage = $request->session()->get('language');

        if ($userLanguage && $userLanguage !== $sessionLanguage) {
            $request->session()->put('language', $userLanguage);
        }
    }
}
