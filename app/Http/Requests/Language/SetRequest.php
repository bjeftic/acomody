<?php

namespace App\Http\Requests\Language;

use Illuminate\Foundation\Http\FormRequest;

class SetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        $supported = implode(',', array_column(config('language.ui_languages', []), 'code'));

        return [
            'language' => "required|string|in:{$supported}",
        ];
    }
}
