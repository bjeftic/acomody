<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stevebauman\Location\Facades\Location;

class LanguageService
{
    /**
     * Get the active UI language for the current user:
     * 1. Authenticated user's preference
     * 2. Session language
     * 3. Auto-detect from Accept-Language header / IP
     * 4. Default (en)
     */
    public static function getUserLanguage(): string
    {
        $user = Auth::user();

        if ($user && $user->preferred_language && self::isSupported($user->preferred_language)) {
            return $user->preferred_language;
        }

        if (session()->has('language')) {
            $sessionLanguage = session()->get('language');
            if (self::isSupported($sessionLanguage)) {
                return $sessionLanguage;
            }
        }

        return self::detectLanguage();
    }

    /**
     * Detect language from IP geolocation or Accept-Language header.
     */
    public static function detectLanguage(?string $countryCode = null): string
    {
        if ($countryCode) {
            $mapped = config('constants.country_language_map')[$countryCode] ?? null;
            if ($mapped && self::isSupported($mapped)) {
                return $mapped;
            }
        }

        try {
            $position = Location::get();
            $detected = config('constants.country_language_map')[$position?->countryCode] ?? null;
            if ($detected && self::isSupported($detected)) {
                return $detected;
            }
        } catch (\Throwable) {
            // Fall through to default
        }

        return config('language.default', 'en');
    }

    /**
     * Set the user's language preference.
     */
    public function setUserLanguage(?User $user, string $languageCode, Request $request): bool
    {
        if (! self::isSupported($languageCode)) {
            return false;
        }

        if ($user) {
            $user->update(['preferred_language' => $languageCode]);

            Log::info('User manually changed language', [
                'user_id' => $user->id,
                'language' => $languageCode,
                'previous' => $user->getOriginal('preferred_language'),
            ]);
        }

        $request->session()->put('language', $languageCode);

        return true;
    }

    /**
     * All UI languages available in the app.
     *
     * @return array<int, array{code: string, name: string, native: string}>
     */
    public static function getUiLanguages(): array
    {
        return config('language.ui_languages', []);
    }

    public static function isSupported(string $code): bool
    {
        return collect(self::getUiLanguages())->contains('code', $code);
    }

    /**
     * Apply the given language to Laravel's locale.
     */
    public static function applyLocale(string $languageCode): void
    {
        App::setLocale($languageCode);
    }
}
