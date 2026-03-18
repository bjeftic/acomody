<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class FeaturedAccommodationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'sortBy' => 'string|in:rating,reviews,price_asc,price_desc,newest',
            'page' => 'integer|min:1',
            'perPage' => 'integer|min:1|max:100',
            'location_id' => 'string|exists:locations,id',
        ];
    }
}
