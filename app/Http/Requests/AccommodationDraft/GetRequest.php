<?php

namespace App\Http\Requests\AccommodationDraft;

use Illuminate\Foundation\Http\FormRequest;

class GetRequest extends FormRequest
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
            'status' => [
                'sometimes',
                'string',
                'in:draft,waiting_for_approval,published'
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'status' => 'draft status',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status.in' => 'The :attribute must be one of: draft, active, inactive, or published.',
        ];
    }

    /**
     * Get the validated status with a default value.
     */
    public function getStatus(): string
    {
        return $this->validated()['status'] ?? 'draft';
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Normalize status to lowercase
        if ($this->has('status')) {
            $this->merge([
                'status' => strtolower($this->query('status'))
            ]);
        }
    }
}
