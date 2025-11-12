<?php

namespace App\Http\Requests\AccommodationDraft;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\AccommodationDraft;

class CreateRequest extends FormRequest
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
            'data' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    $exists = \App\Models\AccommodationDraft::where('user_id', $this->user()->id)
                        ->where('status', 'draft')
                        ->exists();

                    if ($exists) {
                        $fail('You already have an active draft.');
                    }
                },
            ],
        ];
    }
}
