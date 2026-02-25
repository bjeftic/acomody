<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class CalculatePriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'check_in'           => ['required', 'date', 'after_or_equal:today'],
            'check_out'          => ['required', 'date', 'after:check_in'],
            'guests'             => ['required', 'integer', 'min:1'],
            'optional_fee_ids'   => ['nullable', 'array'],
            'optional_fee_ids.*' => ['ulid', 'exists:fees,id'],
            'guest_ages'         => ['nullable', 'array'],
            'guest_ages.*'       => ['integer', 'min:0', 'max:120'],
        ];
    }
}
