<?php

namespace Database\Factories;

use App\Enums\Accommodation\AccommodationOccupation;
use App\Models\User;
use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Amenity;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AccommodationDraft>
 */
class AccommodationDraftFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(['draft', 'waiting_for_approval', 'published']),
            'data' => json_encode([
                'accommodation_type'       => $this->faker->numberBetween(1, 5),
                'accommodation_occupation' => AccommodationOccupation::cases()[array_rand(AccommodationOccupation::cases())]->value,

                'address' => [
                    'country'  => Country::inRandomOrder()->first()->iso_code_2,
                    'street'   => $this->faker->streetAddress(),
                    'city'     => $this->faker->city(),
                    'state'    => null,
                    'zip_code' => $this->faker->postcode(),
                ],

                'coordinates' => [
                    'latitude'  => $this->faker->latitude(44.7, 44.9),
                    'longitude' => $this->faker->longitude(20.3, 20.6),
                ],

                'floor_plan' => [
                    'guests'    => $this->faker->numberBetween(1, 8),
                    'bedrooms'  => $this->faker->numberBetween(1, 4),
                    'beds'      => $this->faker->numberBetween(1, 6),
                    'bathrooms' => $this->faker->numberBetween(1, 3),
                ],

                'amenities' => Amenity::inRandomOrder()->limit(4)->pluck('id')->toArray(),

                'title'       => $this->faker->sentence(4),
                'description' => $this->faker->paragraph(4),

                'pricing' => [
                    'basePrice'            => $this->faker->numberBetween(20, 120),
                    // 'hasWeekendPrice'      => false,
                    // 'weekendPrice'         => 0,
                    // 'weeklyDiscount'       => 0,
                    // 'monthlyDiscount'      => 0,
                    'bookingType'          => $this->faker->randomElement(['instant-booking', 'request-to-book']),
                    'minStay'              => 1,
                    // 'hasDaySpecificMinStay'=> false,
                    // 'daySpecificMinStay'   => [
                    //     'monday'    => ['enabled' => false, 'nights' => 1],
                    //     'tuesday'   => ['enabled' => false, 'nights' => 1],
                    //     'wednesday' => ['enabled' => false, 'nights' => 1],
                    //     'thursday'  => ['enabled' => false, 'nights' => 1],
                    //     'friday'    => ['enabled' => false, 'nights' => 3],
                    //     'saturday'  => ['enabled' => false, 'nights' => 2],
                    //     'sunday'    => ['enabled' => false, 'nights' => 1],
                    // ],
                    // 'standardFees' => [
                    //     [
                    //         'feeType'    => 'cleaning',
                    //         'amount'     => 10,
                    //         'chargeType' => 'per_booking',
                    //         'isOptional' => false,
                    //     ],
                    //     [
                    //         'feeType'    => 'guest_service',
                    //         'amount'     => 5,
                    //         'chargeType' => 'percentage',
                    //         'isOptional' => false,
                    //     ],
                    // ],
                    // 'amenityFees' => [
                    //     [
                    //         'feeType'    => 'amenity',
                    //         'name'       => Amenity::where('is_feeable', true)->inRandomOrder()->first()->id,
                    //         'amount'     => 5,
                    //         'chargeType' => 'per_unit',
                    //     ],
                    //     [
                    //         'feeType'    => 'amenity',
                    //         'name'       => Amenity::where('is_feeable', true)->inRandomOrder()->first()->id,
                    //         'amount'     => 5,
                    //         'chargeType' => 'per_booking',
                    //     ],
                    // ],
                    // 'customFees' => [
                    //     [
                    //         'feeType'     => 'custom',
                    //         'name'        => 'Some fee',
                    //         'description' => 'Some fee',
                    //         'amount'      => 5,
                    //         'chargeType'  => 'per_unit',
                    //         'isOptional'  => false,
                    //     ],
                    // ],
                ],
                'house_rules' => [
                    'checkInFrom'     => '15:00',
                    'checkInUntil'    => '20:00',
                    'checkOutUntil'   => '11:00',
                    'hasQuietHours'   => false,
                    'quietHoursFrom'  => '22:00',
                    'quietHoursUntil' => '08:00',
                    'additionalRules' => null,
                    'cancellationPolicy' => 'moderate',
                ],
            ]),

            'current_step'   => 12,
            'last_saved_at'  => now(),
        ];
    }
}
