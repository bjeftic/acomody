<?php

namespace App\Http\Requests\AccommodationDraft;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class SaveDraftRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_step' => 'required|integer|min:1',
            'data' => 'required|array',
        ];
    }
}
