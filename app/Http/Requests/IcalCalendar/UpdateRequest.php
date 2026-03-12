<?php

namespace App\Http\Requests\IcalCalendar;

use App\Enums\Ical\IcalSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'source' => ['sometimes', 'string', new Enum(IcalSource::class)],
            'name' => ['nullable', 'string', 'max:255'],
            'ical_url' => ['sometimes', 'url', 'max:2048'],
            'is_active' => ['boolean'],
        ];
    }
}
