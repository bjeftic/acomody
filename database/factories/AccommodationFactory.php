<?php

namespace Database\Factories;

use App\Enums\Accommodation\AccommodationOccupation;
use App\Enums\Accommodation\AccommodationType;
use App\Enums\Accommodation\BedType;
use App\Enums\Accommodation\BookingType;
use App\Enums\PriceableItem\PricingType;
use App\Models\Accommodation;
use App\Models\AccommodationBed;
use App\Models\AccommodationDraft;
use App\Models\Amenity;
use App\Models\Currency;
use App\Models\Location;
use App\Models\Photo;
use App\Models\PriceableItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Accommodation>
 */
class AccommodationFactory extends Factory
{
    protected $model = Accommodation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locationId = Location::inRandomOrder()->first()?->id ?? Location::factory()->create()->id;

        return [
            'accommodation_type' => AccommodationType::cases()[array_rand(AccommodationType::cases())]->value,
            'accommodation_occupation' => AccommodationOccupation::cases()[array_rand(AccommodationOccupation::cases())]->value,
            'title' => $this->multilocaleTitle(),
            'description' => $this->multilocaleDescription(),
            'booking_type' => BookingType::cases()[array_rand(BookingType::cases())]->value,
            'cancellation_policy' => fake()->randomElement(['flexible', 'moderate', 'firm', 'strict']),
            'max_guests' => fake()->numberBetween(1, 10),
            'location_id' => $locationId,
            'user_id' => User::inRandomOrder()->first()->id,
            'latitude' => fake()->latitude(41, 44),
            'longitude' => fake()->longitude(19, 22),
            'street_address' => fake()->address(),
            'is_active' => fake()->boolean(95),
            'approved_by' => User::where('is_superadmin', true)->inRandomOrder()->first()?->id,
            'views_count' => fake()->numberBetween(0, 10000),
            'favorites_count' => fake()->numberBetween(0, 5000),
            'is_featured' => fake()->boolean(10),
            'bedrooms' => fake()->numberBetween(1, 4),
            'bathrooms' => fake()->numberBetween(1, 2),
            'check_in_from' => fake()->time(),
            'check_in_until' => fake()->time(),
            'quiet_hours_from' => fake()->time(),
            'quiet_hours_until' => fake()->time(),
        ];
    }

    /** @return array<string, string> */
    private function multilocaleTitle(): array
    {
        return [
            'en' => fake()->randomElement([
                'Cozy apartment in the city center',
                'Modern studio near the old town',
                'Charming flat with mountain views',
                'Bright 2BR apartment with balcony',
                'Peaceful retreat in a quiet neighborhood',
                'Stylish loft in the heart of the city',
                'Luxury villa with private pool',
                'Rustic cabin nestled in the woods',
                'Elegant suite with sea views',
                'Family-friendly home near the park',
            ]),
            'sr' => fake()->randomElement([
                'Udoban stan u centru grada',
                'Moderan studio blizu starog dela grada',
                'Šarmantan stan sa pogledom na planinu',
                'Svetao dvosobni stan sa balkonom',
                'Miran smeštaj u tihom kvartu',
                'Stilski loft u srcu grada',
                'Luksuzna vila sa privatnim bazenom',
                'Rustikalna koliba usred šume',
                'Elegantna soba sa pogledom na more',
                'Smeštaj pogodan za porodice blizu parka',
            ]),
            'hr' => fake()->randomElement([
                'Ugodan stan u centru grada',
                'Moderan studio blizu starog dijela grada',
                'Šarmantan stan s pogledom na planinu',
                'Svijetao dvosobni stan s balkonom',
                'Miran smještaj u tihoj četvrti',
                'Stilski loft u srcu grada',
                'Luksuzna vila s privatnim bazenom',
                'Rustikalna koliba usred šume',
                'Elegantna soba s pogledom na more',
                'Smještaj pogodan za obitelji blizu parka',
            ]),
            'mk' => fake()->randomElement([
                'Удобен стан во центарот на градот',
                'Модерно студио блиску до старото место',
                'Шармантен стан со поглед на планината',
                'Светол двособен стан со балкон',
                'Мирен сместувачки простор во тивок кварт',
                'Стилски лофт во срцето на градот',
                'Луксузна вила со приватен базен',
                'Рустична колиба среде шума',
                'Елегантна соба со поглед на море',
                'Простор погоден за семејства',
            ]),
            'sl' => fake()->randomElement([
                'Udobno stanovanje v središču mesta',
                'Moderno studio stanovanje blizu starega mesta',
                'Šarmantno stanovanje s pogledom na gore',
                'Svetlo dvosobno stanovanje z balkonom',
                'Miren prostor v tihi soseski',
                'Stilsko podstrešno stanovanje v srcu mesta',
                'Luksuzna vila z zasebnim bazenom',
                'Rustikalna koča sredi gozda',
                'Elegantna soba s pogledom na morje',
                'Bivališče za družine v bližini parka',
            ]),
        ];
    }

    /** @return array<string, string> */
    private function multilocaleDescription(): array
    {
        return [
            'en' => fake()->paragraph(3),
            'sr' => fake()->randomElement([
                'Dobrodošli u naš lep smeštaj! Prostor je udobno opremljen i idealan za porodice, parove i poslovne putnike. Uživajte u sjajnoj lokaciji sa lakim pristupom svim atrakcijama.',
                'Naš smeštaj nudi sve što vam je potrebno za udoban boravak. Moderno opremljen prostor sa odličnom lokacijom u centru grada.',
                'Lep i udoban smeštaj sa svim potrebnim sadržajima. Idealno za kraće i duže boravke, blizu svih glavnih atrakcija.',
            ]),
            'hr' => fake()->randomElement([
                'Dobrodošli u naš lijepi smještaj! Prostor je udobno opremljen i idealan za obitelji, parove i poslovne putnike. Uživajte u sjajnoj lokaciji.',
                'Naš smještaj nudi sve što vam je potrebno za ugodan boravak. Moderno opremljen prostor s odličnom lokacijom u centru grada.',
                'Lijep i ugodan smještaj sa svim potrebnim sadržajima. Idealno za kraće i duže boravke, blizu svih glavnih atrakcija.',
            ]),
            'mk' => fake()->randomElement([
                'Добредојдовте во нашиот убав сместувачки простор! Просторот е удобно опремен и идеален за семејства, парови и деловни патници.',
                'Нашиот сместувачки простор нуди сè што ви е потребно за удобен престој. Модерно опремен простор со одлична локација.',
                'Убав и удобен сместувачки простор со сите потребни содржини. Идеално за пократок и подолг престој.',
            ]),
            'sl' => fake()->randomElement([
                'Dobrodošli v naše lepo bivališče! Prostor je udobno opremljen in idealen za družine, pare in poslovne potnike.',
                'Naše bivališče ponuja vse, kar potrebujete za udobno bivanje. Moderno opremljen prostor z odlično lokacijo v središču mesta.',
                'Lepo in udobno bivališče z vsemi potrebnimi ugodnostmi. Idealno za krajša in daljša bivanja.',
            ]),
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function (Accommodation $accommodation) {
            if (! isset($accommodation->user_id)) {
                $accommodation->user_id = User::factory()->create()->id;
            }

            $draft = AccommodationDraft::factory()->create([
                'user_id' => $accommodation->user_id,
                'status' => 'published',
            ]);

            $accommodation->accommodation_draft_id = $draft->id;
        })->afterCreating(function (Accommodation $accommodation) {
            $currencyCode = fake()->randomElement(['EUR', 'USD', 'RSD']);
            $currency = Currency::where('code', $currencyCode)->first();

            if ($currency->code === 'EUR' || $currency->code === 'USD') {
                $price = fake()->numberBetween(30, 120);
                $basePriceEur = $currency->code === 'EUR' ? $price : round($price / 1.1, 2);
            }
            if ($currency->code === 'RSD') {
                $price = fake()->numberBetween(3000, 12000);
                $basePriceEur = round($price / 117, 2);
            }
            PriceableItem::create([
                'pricing_type' => PricingType::NIGHTLY,
                'base_price' => $price,
                'currency_id' => $currency->id,
                'base_price_eur' => $basePriceEur,
                'priceable_type' => Accommodation::class,
                'priceable_id' => $accommodation->id,
                'min_quantity' => 1,
                'max_quantity' => null,
                'is_active' => true,
            ]);

            $amenityIds = Amenity::inRandomOrder()
                ->where('category', 'essential')
                ->limit(fake()->numberBetween(2, 5))
                ->pluck('id')
                ->merge(
                    Amenity::inRandomOrder()
                        ->whereNot('category', 'essential')
                        ->limit(fake()->numberBetween(10, 15))
                        ->pluck('id')
                );

            $accommodation->amenities()->attach($amenityIds);

            collect(BedType::cases())
                ->shuffle()
                ->take(fake()->numberBetween(1, 3))
                ->each(fn (BedType $bedType) => AccommodationBed::create([
                    'accommodation_id' => $accommodation->id,
                    'bed_type' => $bedType->value,
                    'quantity' => fake()->numberBetween(1, 4),
                ]));

            // Create photos (5-12 photos per accommodation)
            $photoCount = fake()->numberBetween(5, 12);

            Photo::factory()
                ->count($photoCount)
                ->forAccommodation() // Use accommodation photos disk
                ->withPicsumPaths()
                ->sequence(fn ($sequence) => [
                    'order' => $sequence->index,
                    'is_primary' => $sequence->index === 0, // First photo is primary
                ])
                ->create([
                    'photoable_type' => Accommodation::class,
                    'photoable_id' => $accommodation->id,
                ]);
        });
    }
}
