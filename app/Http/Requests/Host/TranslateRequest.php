<?php

namespace App\Http\Requests\Host;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TranslateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $supportedLocales = array_column(config('constants.supported_locales'), 'code');

        return [
            'text' => ['required', 'string', 'max:2000'],
            'target_locale' => ['required', 'string', Rule::in($supportedLocales)],
        ];
    }
}
