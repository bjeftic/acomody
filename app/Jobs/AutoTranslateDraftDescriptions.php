<?php

namespace App\Jobs;

use App\Models\AccommodationDraft;
use App\Services\TranslationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class AutoTranslateDraftDescriptions implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        private readonly string $draftId,
    ) {}

    public function handle(TranslationService $translationService): void
    {
        $draft = AccommodationDraft::find($this->draftId);

        if (! $draft) {
            return;
        }

        $data = json_decode($draft->data, true);
        $locales = array_column(config('constants.supported_locales'), 'code');
        $translations = is_array($data['description'] ?? null) ? $data['description'] : [];

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
                Log::warning('AutoTranslateDraftDescriptions: failed to translate', [
                    'draft_id' => $this->draftId,
                    'target_locale' => $code,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($hasChanged) {
            $data['description'] = $translations;
            $draft->update(['data' => json_encode($data)]);
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
