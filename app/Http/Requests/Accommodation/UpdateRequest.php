<?php

namespace App\Http\Requests\Accommodation;

use App\Enums\Accommodation\AccommodationOccupation;
use App\Enums\Accommodation\AccommodationType;
use App\Enums\Accommodation\BedType;
use App\Enums\Accommodation\BookingType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $accommodation = $this->route('accommodation');

        return $this->user() && $accommodation->user_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'accommodation_type' => ['required', Rule::enum(AccommodationType::class)],
            'accommodation_occupation' => ['required', Rule::enum(AccommodationOccupation::class)],

            'address' => ['required', 'array'],
            'address.street' => ['required', 'string', 'max:255'],

            'coordinates' => ['required', 'array'],
            'coordinates.latitude' => ['required', 'numeric', 'between:-90,90'],
            'coordinates.longitude' => ['required', 'numeric', 'between:-180,180'],

            'floor_plan' => ['required', 'array'],
            'floor_plan.guests' => ['required', 'integer', 'min:1', 'max:16'],
            'floor_plan.bedrooms' => ['required', 'integer', 'min:0', 'max:50'],
            'floor_plan.bathrooms' => ['required', 'integer', 'min:0', 'max:20'],
            'floor_plan.bed_types' => ['present', 'array'],
            'floor_plan.bed_types.*.bed_type' => ['required', Rule::enum(BedType::class)],
            'floor_plan.bed_types.*.quantity' => ['required', 'integer', 'min:1', 'max:20'],

            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['integer', 'exists:amenities,id'],

            'title' => ['required', 'string', 'min:10', 'max:255'],
            'description' => ['required', 'string', 'min:50', 'max:5000'],

            'pricing' => ['required', 'array'],
            'pricing.basePrice' => ['required', 'numeric', 'min:10', 'max:10000'],
            'pricing.bookingType' => ['required', Rule::enum(BookingType::class)],
            'pricing.minStay' => ['required', 'integer', 'min:1', 'max:365'],

            'house_rules' => ['required', 'array'],
            'house_rules.checkInFrom' => ['required', 'string', 'date_format:H:i'],
            'house_rules.checkInUntil' => ['required', 'string', 'date_format:H:i'],
            'house_rules.checkOutUntil' => ['required', 'string', 'date_format:H:i'],
            'house_rules.hasQuietHours' => ['required', 'boolean'],
            'house_rules.quietHoursFrom' => ['nullable', 'string', 'date_format:H:i'],
            'house_rules.quietHoursUntil' => ['nullable', 'string', 'date_format:H:i'],
            'house_rules.cancellationPolicy' => ['required', 'string', Rule::in(['flexible', 'moderate', 'firm', 'strict', 'non-refundable'])],
        ];
    }
}
