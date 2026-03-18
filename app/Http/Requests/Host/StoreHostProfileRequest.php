<?php

namespace App\Http\Requests\Host;

use Illuminate\Foundation\Http\FormRequest;

class StoreHostProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'display_name' => 'required|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'contact_email' => 'required|email|max:255',
            'phone' => 'required|string|max:30',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:255',
            'country_id' => 'nullable|integer|exists:countries,id',
            'tax_id' => 'nullable|string|max:100',
            'vat_number' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:2000',
        ];
    }
}
