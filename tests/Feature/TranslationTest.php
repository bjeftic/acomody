<?php

use App\Services\TranslationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

// ============================================================
// TranslationService
// ============================================================

describe('TranslationService', function () {
    beforeEach(function () {
        config(['services.langbly.api_key' => 'test-api-key']);
        Http::preventStrayRequests();
        Cache::flush();
    });

    it('translates text via Langbly API', function () {
        Http::fake([
            '*/language/translate/v2' => Http::response([
                'data' => ['translations' => [['translatedText' => 'Hello']]],
            ]),
        ]);

        $result = app(TranslationService::class)->translate('Zdravo', 'en');

        expect($result)->toBe('Hello');
    });

    it('throws when Langbly returns an error', function () {
        Http::fake([
            '*/language/translate/v2' => Http::response('Internal Server Error', 500),
        ]);

        expect(fn () => app(TranslationService::class)->translate('text', 'sr'))
            ->toThrow(\RuntimeException::class);
    });

    it('fills only empty locales in translateMissing', function () {
        Http::fake([
            '*/language/translate/v2' => Http::response([
                'data' => ['translations' => [['translatedText' => 'Translated']]],
            ]),
        ]);

        $result = app(TranslationService::class)->translateMissing(
            ['en' => 'Hello', 'sr' => 'Zdravo', 'hr' => ''],
            ['en', 'sr', 'hr']
        );

        expect($result['en'])->toBe('Hello')
            ->and($result['sr'])->toBe('Zdravo')
            ->and($result['hr'])->toBe('Translated');

        Http::assertSentCount(1);
    });

    it('tracks daily usage per user', function () {
        $service = app(TranslationService::class);
        $userId = 'test-user-123';
        $limit = config('services.langbly.daily_limit');

        expect($service->canTranslate($userId))->toBeTrue()
            ->and($service->remainingTranslations($userId))->toBe($limit);

        $service->incrementUsage($userId);
        $service->incrementUsage($userId);

        expect($service->remainingTranslations($userId))->toBe($limit - 2);
    });

    it('blocks translation when daily limit is reached', function () {
        $service = app(TranslationService::class);
        $userId = 'limited-user';
        $limit = config('services.langbly.daily_limit');

        Cache::put("langbly_daily_{$userId}_".today()->format('Y-m-d'), $limit, now()->endOfDay());

        expect($service->canTranslate($userId))->toBeFalse()
            ->and($service->remainingTranslations($userId))->toBe(0);
    });
});

// ============================================================
// POST /api/host/translations/translate
// ============================================================

describe('POST /api/host/translations/translate', function () {
    beforeEach(function () {
        config(['services.langbly.api_key' => 'test-api-key']);
        Http::preventStrayRequests();
        Cache::flush();
    });

    it('translates text for authenticated user', function () {
        Http::fake([
            '*/language/translate/v2' => Http::response([
                'data' => ['translations' => [['translatedText' => 'Zdravo']]],
            ]),
        ]);

        authenticatedUser();

        $this->postJson('/api/host/translations/translate', [
            'text' => 'Hello',
            'target_locale' => 'sr',
        ])
            ->assertOk()
            ->assertJsonFragment(['translated_text' => 'Zdravo'])
            ->assertJsonStructure(['translated_text', 'remaining_today']);
    });

    it('returns 429 when daily limit is reached', function () {
        $user = authenticatedUser();
        $limit = config('services.langbly.daily_limit');

        Cache::put("langbly_daily_{$user->id}_".today()->format('Y-m-d'), $limit, now()->endOfDay());

        $this->postJson('/api/host/translations/translate', [
            'text' => 'Hello',
            'target_locale' => 'sr',
        ])
            ->assertStatus(429)
            ->assertJsonFragment(['remaining_today' => 0]);
    });

    it('rejects unsupported target_locale', function () {
        authenticatedUser();

        $this->postJson('/api/host/translations/translate', [
            'text' => 'Hello',
            'target_locale' => 'xx',
        ])->assertUnprocessable();
    });

    it('requires authentication', function () {
        $this->postJson('/api/host/translations/translate', [
            'text' => 'Hello',
            'target_locale' => 'sr',
        ])->assertUnauthorized();
    });
});
