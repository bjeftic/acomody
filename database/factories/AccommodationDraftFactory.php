<?php

namespace Database\Factories;

use App\Enums\Accommodation\AccommodationOccupation;
use App\Enums\Accommodation\AccommodationType;
use App\Models\Amenity;
use App\Models\Country;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AccommodationDraft>
 */
class AccommodationDraftFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(['draft', 'waiting_for_approval', 'processing', 'published', 'rejected']),
            'data' => json_encode([
                'accommodation_type' => AccommodationType::cases()[array_rand(AccommodationType::cases())]->value,
                'accommodation_occupation' => AccommodationOccupation::cases()[array_rand(AccommodationOccupation::cases())]->value,

                'address' => [
                    'country' => Country::inRandomOrder()->first()->iso_code_2,
                    'street' => $this->faker->streetAddress(),
                    'city' => $this->faker->city(),
                    'state' => null,
                    'zip_code' => $this->faker->postcode(),
                ],

                'coordinates' => [
                    'latitude' => $this->faker->latitude(19, 22),
                    'longitude' => $this->faker->longitude(42, 44),
                ],

                'floor_plan' => [
                    'guests' => $this->faker->numberBetween(1, 8),
                    'bedrooms' => $this->faker->numberBetween(1, 4),
                    'bathrooms' => $this->faker->numberBetween(1, 3),
                    'bed_types' => collect(\App\Enums\Accommodation\BedType::cases())
                        ->shuffle()
                        ->take(2)
                        ->map(fn ($bt) => [
                            'bed_type' => $bt->value,
                            'quantity' => $this->faker->numberBetween(1, 3),
                        ])
                        ->values()
                        ->toArray(),
                ],

                'amenities' => Amenity::inRandomOrder()->limit(4)->pluck('id')->toArray(),

                'title' => $this->multilocaleTitle(),
                'description' => $this->multilocaleDescription(),

                'pricing' => [
                    'basePrice' => $this->faker->numberBetween(20, 120),
                    // 'hasWeekendPrice'      => false,
                    // 'weekendPrice'         => 0,
                    // 'weeklyDiscount'       => 0,
                    // 'monthlyDiscount'      => 0,
                    'bookingType' => $this->faker->randomElement(['instant_booking', 'request_to_book']),
                    'minStay' => 1,
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
                    'checkInFrom' => '15:00',
                    'checkInUntil' => '20:00',
                    'checkOutUntil' => '11:00',
                    'hasQuietHours' => false,
                    'quietHoursFrom' => '22:00',
                    'quietHoursUntil' => '08:00',
                    'cancellationPolicy' => 'moderate',
                ],
            ]),

            'current_step' => 12,
            'last_saved_at' => now(),
        ];
    }

    /** @return array<string, string> */
    private function multilocaleTitle(): array
    {
        $titles = [
            'en' => $this->faker->randomElement([
                'Cozy apartment in the city center',
                'Modern studio near the old town',
                'Charming flat with mountain views',
                'Bright 2BR apartment with balcony',
                'Peaceful retreat in a quiet neighborhood',
            ]),
            'sr' => $this->faker->randomElement([
                'Udoban stan u centru grada',
                'Moderan studio blizu starog dela grada',
                'Šarmantan stan sa pogledom na planinu',
                'Svetao dvosobni stan sa balkonom',
                'Miran smeštaj u tihom kvartu',
            ]),
            'hr' => $this->faker->randomElement([
                'Ugodan stan u centru grada',
                'Moderan studio blizu starog dijela grada',
                'Šarmantan stan s pogledom na planinu',
                'Svijetao dvosobni stan s balkonom',
                'Miran smještaj u tihoj četvrti',
            ]),
            'mk' => $this->faker->randomElement([
                'Удобен стан во центарот на градот',
                'Модерно студио блиску до старото место',
                'Шармантен стан со поглед на планината',
                'Светол двособен стан со балкон',
                'Мирен сместувачки простор во тивок кварт',
            ]),
            'sl' => $this->faker->randomElement([
                'Udobno stanovanje v središču mesta',
                'Moderno studio stanovanje blizu starega mesta',
                'Šarmantno stanovanje s pogledom na gore',
                'Svetlo dvosobno stanovanje z balkonom',
                'Miren prostor v tihi soseski',
            ]),
        ];

        return $titles;
    }

    /** @return array<string, string> */
    private function multilocaleDescription(): array
    {
        $descriptions = [
            'en' => $this->faker->paragraph(3),
            'sr' => $this->faker->randomElement([
                'Dobrodošli u naš lep smeštaj! Prostor je udobno opremljen i idealan za porodice, parove i poslovne putnike. Uživajte u sjajnoj lokaciji sa lakim pristupom svim atrakcijama.',
                'Naš smeštaj nudi sve što vam je potrebno za udoban boravak. Moderno opremljen prostor sa odličnom lokacijom u centru grada.',
                'Lep i udoban smeštaj sa svim potrebnim sadržajima. Idealno za kraće i duže boravke, blizu svih glavnih atrakcija.',
            ]),
            'hr' => $this->faker->randomElement([
                'Dobrodošli u naš lijepi smještaj! Prostor je udobno opremljen i idealan za obitelji, parove i poslovne putnike. Uživajte u sjajnoj lokaciji.',
                'Naš smještaj nudi sve što vam je potrebno za ugodan boravak. Moderno opremljen prostor s odličnom lokacijom u centru grada.',
                'Lijep i ugodan smještaj sa svim potrebnim sadržajima. Idealno za kraće i duže boravke, blizu svih glavnih atrakcija.',
            ]),
            'mk' => $this->faker->randomElement([
                'Добредојдовте во нашиот убав сместувачки простор! Просторот е удобно опремен и идеален за семејства, парови и деловни патници.',
                'Нашиот сместувачки простор нуди сè што ви е потребно за удобен престој. Модерно опремен простор со одлична локација.',
                'Убав и удобен сместувачки простор со сите потребни содржини. Идеално за пократок и подолг престој.',
            ]),
            'sl' => $this->faker->randomElement([
                'Dobrodošli v naše lepo bivališče! Prostor je udobno opremljen in idealen za družine, pare in poslovne potnike.',
                'Naše bivališče ponuja vse, kar potrebujete za udobno bivanje. Moderno opremljen prostor z odlično lokacijo v središču mesta.',
                'Lepo in udobno bivališče z vsemi potrebnimi ugodnostmi. Idealno za krajša in daljša bivanja.',
            ]),
        ];

        return $descriptions;
    }
}
