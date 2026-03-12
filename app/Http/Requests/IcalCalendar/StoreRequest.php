<?php

namespace App\Http\Requests\IcalCalendar;

use App\Enums\Ical\IcalSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'source' => ['required', 'string', new Enum(IcalSource::class)],
            'name' => ['nullable', 'string', 'max:255'],
            'ical_url' => ['required', 'url', 'max:2048'],
            'is_active' => ['boolean'],
        ];
    }
}
