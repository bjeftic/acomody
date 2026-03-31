<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TranslationService
{
    private ?string $apiKey;

    private string $baseUrl;

    private int $dailyLimit;

    public function __construct()
    {
        $this->apiKey = config('services.langbly.api_key');
        $this->baseUrl = config('services.langbly.base_url');
        $this->dailyLimit = config('services.langbly.daily_limit');
    }

    /**
     * Translate a single text to the given locale.
     */
    public function translate(string $text, string $targetLocale, ?string $sourceLocale = null): string
    {
        $payload = ['q' => $text, 'target' => $targetLocale];

        if ($sourceLocale !== null) {
            $payload['source'] = $sourceLocale;
        }

        if ($this->apiKey === null) {
            throw new \RuntimeException('Langbly API key is not configured.');
        }

        $response = Http::withHeader('X-API-Key', $this->apiKey)
            ->post("{$this->baseUrl}/language/translate/v2", $payload);

        if ($response->failed()) {
            throw new \RuntimeException('Langbly translation failed: '.$response->body());
        }

        return (string) $response->json('data.translations.0.translatedText', '');
    }

    /**
     * Fill empty locale slots in the given translations array.
     * Only translates locales that are empty/missing — never overwrites existing values.
     *
     * @param  array<string, string>  $translations  Existing translations keyed by locale code.
     * @param  string[]  $locales  All locale codes that must be present.
     * @return array<string, string>
     */
    public function translateMissing(array $translations, array $locales): array
    {
        [$sourceLocale, $sourceText] = $this->findSource($translations, $locales);

        if ($sourceText === null) {
            return $translations;
        }

        foreach ($locales as $code) {
            if (empty($translations[$code])) {
                $translations[$code] = $this->translate($sourceText, $code, $sourceLocale);
            }
        }

        return $translations;
    }

    public function canTranslate(string $userId): bool
    {
        return $this->getUsageCount($userId) < $this->dailyLimit;
    }

    public function remainingTranslations(string $userId): int
    {
        return max(0, $this->dailyLimit - $this->getUsageCount($userId));
    }

    public function incrementUsage(string $userId): void
    {
        $key = $this->cacheKey($userId);

        if (! Cache::has($key)) {
            Cache::put($key, 1, now()->endOfDay());
        } else {
            Cache::increment($key);
        }
    }

    private function getUsageCount(string $userId): int
    {
        return (int) Cache::get($this->cacheKey($userId), 0);
    }

    private function cacheKey(string $userId): string
    {
        return 'langbly_daily_'.$userId.'_'.today()->format('Y-m-d');
    }

    /**
     * Find the best non-empty source locale and text from the translations array.
     * Prefers 'en' if available, otherwise uses the first non-empty entry.
     *
     * @param  array<string, string>  $translations
     * @param  string[]  $locales
     * @return array{0: string|null, 1: string|null}
     */
    private function findSource(array $translations, array $locales): array
    {
        if (! empty($translations['en'])) {
            return ['en', $translations['en']];
        }

        foreach ($locales as $code) {
            if (! empty($translations[$code])) {
                return [$code, $translations[$code]];
            }
        }

        return [null, null];
    }
}
