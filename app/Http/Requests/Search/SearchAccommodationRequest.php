<?php

namespace App\Http\Requests\Search;

use App\Models\Currency;
use Illuminate\Foundation\Http\FormRequest;

class SearchAccommodationRequest extends FormRequest
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
        $activeCurrencyCodes = Currency::where('is_active', true)->pluck('code')->toArray();

        $currenciesValidation = [];

        foreach ($activeCurrencyCodes as $code) {
            $currenciesValidation["priceRange_{$code}.min"] = "numeric|min:0";
            $currenciesValidation["priceRange_{$code}.max"] = "numeric|gte:priceRange_{$code}.min";
        }

        return [
            // Location search option 1
            'location.id' => 'required_without:bounds|integer',
            'location.name' => 'required_without:bounds|string|max:255',

            // Location search option 2
            'bounds' => 'required_without:location.id|array',
            'bounds.northEast' => 'required_with:bounds|array',
            'bounds.northEast.lat' => 'required_with:bounds.northEast|numeric|between:-90,90',
            'bounds.northEast.lng' => 'required_with:bounds.northEast|numeric|between:-180,180',
            'bounds.southWest' => 'required_with:bounds|array',
            'bounds.southWest.lat' => 'required_with:bounds.southWest|numeric|between:-90,90',
            'bounds.southWest.lng' => 'required_with:bounds.southWest|numeric|between:-180,180',

            'checkIn' => 'date_format:Y-m-d',
            'checkOut' => 'date_format:Y-m-d|after:checkIn',
            'guests.adults' => 'integer|min:1',
            'guests.children' => 'integer|min:0',
            'guests.infants' => 'integer|min:0',

            'accommodation_categories' => 'array',
            'accommodation_occupations' => 'array',
            'amenities' => 'array',

            'page' => 'integer|min:1',
            'perPage' => 'integer|min:1|max:100',

        ] + $currenciesValidation;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'location.id.required_without' => 'Either location or map bounds are required',
            'bounds.required_without' => 'Either location or map bounds are required',
            'bounds.northEast.lat.between' => 'Latitude must be between -90 and 90',
            'bounds.northEast.lng.between' => 'Longitude must be between -180 and 180',
            'bounds.southWest.lat.between' => 'Latitude must be between -90 and 90',
            'bounds.southWest.lng.between' => 'Longitude must be between -180 and 180',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $priceRangeCount = 0;

            foreach ($this->all() as $key => $value) {
                if (str_starts_with($key, 'priceRange_') && !is_null($value)) {
                    $priceRangeCount++;
                }
            }

            if ($priceRangeCount > 1) {
                $validator->errors()->add('priceRange', 'Please specify price range for only one currency.');
            }
        });
    }
}
