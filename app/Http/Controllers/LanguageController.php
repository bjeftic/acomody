<?php

namespace App\Http\Controllers;

use App\Http\Requests\Language\SetRequest;
use App\Http\Support\ApiResponse;
use App\Services\LanguageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class LanguageController extends Controller
{
    public function __construct(private readonly LanguageService $languageService) {}

    public function set(SetRequest $request): JsonResponse
    {
        $languageCode = $request->validated('language');
        $user = $request->user();

        $success = $this->languageService->setUserLanguage($user, $languageCode, $request);

        if (! $success) {
            throw ValidationException::withMessages([
                'language' => ['Invalid language code.'],
            ]);
        }

        return ApiResponse::success('Language updated successfully.', null, ['language' => $languageCode]);
    }
}
