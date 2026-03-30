<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Http\Requests\Host\TranslateRequest;
use App\Services\TranslationService;
use Illuminate\Http\JsonResponse;

class TranslationController extends Controller
{
    public function translate(TranslateRequest $request, TranslationService $translationService): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (! $translationService->canTranslate($user->id)) {
            return response()->json([
                'message' => 'Daily translation limit reached.',
                'remaining_today' => 0,
            ], 429);
        }

        $translated = $translationService->translate(
            text: $request->validated('text'),
            targetLocale: $request->validated('target_locale'),
        );

        $translationService->incrementUsage($user->id);

        return response()->json([
            'translated_text' => $translated,
            'remaining_today' => $translationService->remainingTranslations($user->id),
        ]);
    }
}
