<?php

namespace App\Http\Requests\AccommodationDraft;

use App\Enums\Accommodation\AccommodationOccupation;
use App\Enums\Accommodation\AccommodationType;
use App\Enums\Accommodation\BedType;
use App\Enums\Accommodation\BookingType;
use App\Enums\Accommodation\PaymentPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $draft = $this->route('accommodationDraft');

        return in_array($draft->status, ['draft', 'rejected']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isSubmitting = $this->input('status') === 'waiting_for_approval';
        $r = $isSubmitting ? 'required' : 'nullable';

        return [
            'status' => ['required', 'string', Rule::in(['draft', 'waiting_for_approval'])],
            'current_step' => ['required', 'integer', 'min:1'],
            'data' => ['required', 'array'],

            // Step 1: Accommodation type
            'data.accommodation_type' => [$r, Rule::enum(AccommodationType::class)],

            // Step 2: Occupation type
            'data.accommodation_occupation' => [$r, Rule::enum(AccommodationOccupation::class)],

            // Step 3: Address
            'data.address' => [$r, 'array'],
            'data.address.country' => [$r, 'string', 'exists:countries,iso_code_2'],
            'data.address.street' => [$r, 'string', 'max:255'],
            'data.address.city' => [$r, 'string', 'max:255'],
            'data.address.state' => ['nullable', 'string', 'max:255'],
            'data.address.zip_code' => ['nullable', 'string', 'max:20'],

            // Step 3: Coordinates
            'data.coordinates' => [$r, 'array'],
            'data.coordinates.latitude' => [$r, 'numeric', 'between:-90,90'],
            'data.coordinates.longitude' => [$r, 'numeric', 'between:-180,180'],

            // Step 4: Floor plan
            'data.floor_plan' => [$r, 'array'],
            'data.floor_plan.guests' => [$r, 'integer', 'min:1', 'max:16'],
            'data.floor_plan.bedrooms' => [$r, 'integer', 'min:0', 'max:50'],
            'data.floor_plan.bathrooms' => [$r, 'integer', 'min:0', 'max:20'],
            'data.floor_plan.bed_types' => $isSubmitting ? ['required', 'array', 'min:1'] : ['nullable', 'array'],
            'data.floor_plan.bed_types.*.bed_type' => ['required', Rule::enum(BedType::class)],
            'data.floor_plan.bed_types.*.quantity' => ['required', 'integer', 'min:1', 'max:20'],

            // Step 5: Amenities
            'data.amenities' => ['nullable', 'array'],
            'data.amenities.*' => ['integer', 'exists:amenities,id'],

            // Step 7: Title
            'data.title' => $isSubmitting ? ['required', 'array'] : ['nullable', 'array'],
            'data.title.*' => ['nullable', 'string', 'max:255'],

            // Step 8: Description
            'data.description' => $isSubmitting ? ['required', 'array'] : ['nullable', 'array'],
            'data.description.*' => ['nullable', 'string', 'max:5000'],

            // Step 9: Pricing
            'data.pricing' => [$r, 'array'],
            'data.pricing.basePrice' => [$r, 'numeric', 'min:10', 'max:10000'],
            'data.pricing.bookingType' => [$r, Rule::enum(BookingType::class)],
            'data.pricing.minStay' => [$r, 'integer', 'min:1', 'max:365'],

            // Step 10: House rules
            'data.house_rules' => [$r, 'array'],
            'data.house_rules.checkInFrom' => [$r, 'string', 'date_format:H:i'],
            'data.house_rules.checkInUntil' => [$r, 'string', 'date_format:H:i'],
            'data.house_rules.checkOutUntil' => [$r, 'string', 'date_format:H:i'],
            'data.house_rules.hasQuietHours' => [$r, 'boolean'],
            'data.house_rules.quietHoursFrom' => ['nullable', 'string', 'date_format:H:i'],
            'data.house_rules.quietHoursUntil' => ['nullable', 'string', 'date_format:H:i'],
            'data.house_rules.cancellationPolicy' => [$r, 'string', Rule::in(['flexible', 'moderate', 'firm', 'strict', 'non-refundable'])],
            'data.house_rules.paymentPolicy' => ['nullable', Rule::enum(PaymentPolicy::class)],
        ];
    }
}
