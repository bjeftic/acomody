<?php

namespace App\Jobs;

use App\Models\Accommodation;
use App\Services\TranslationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class AutoTranslateMissingDescriptions implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        private readonly string $accommodationId,
    ) {}

    public function handle(TranslationService $translationService): void
    {
        $accommodation = Accommodation::find($this->accommodationId);

        if (! $accommodation) {
            return;
        }

        $locales = array_column(config('constants.supported_locales'), 'code');
        $translations = $accommodation->getTranslations('description');

        $hasChanged = false;

        foreach ($locales as $code) {
            if (! empty($translations[$code])) {
                continue;
            }

            [$sourceLocale, $sourceText] = $this->findSource($translations, $locales);

            if ($sourceText === null) {
                break;
            }

            try {
                $translations[$code] = $translationService->translate($sourceText, $code, $sourceLocale);
                $hasChanged = true;
            } catch (\Throwable $e) {
                Log::warning('AutoTranslateMissingDescriptions: failed to translate', [
                    'accommodation_id' => $this->accommodationId,
                    'target_locale' => $code,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($hasChanged) {
            $accommodation->setTranslations('description', $translations);
            $accommodation->saveQuietly();
        }
    }

    /**
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
