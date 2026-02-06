<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('amenities', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('name');
            $table->string('icon')->nullable();
            $table->enum('category', [
                'essential',
                'kitchen',
                'bedroom',
                'bathroom',
                'heating-cooling',
                'family-children',
                'pets',
                'outdoor',
                'parking-facilities',
                'safety',
                'entertainment',
                'office',
                'location',
                'luxury',
                'services',
            ]);
            $table->boolean('is_feeable')->default(false);
            $table->boolean('is_highlighted')->default(false);
            $table->boolean('is_optional')->default(true);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('is_highlighted');
            $table->index(['slug', 'is_active']);
        });

        $this->seedAmenities();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amenities');
    }

    /**
     * Seed amenities data
     */
    private function seedAmenities(): void
    {
        $amenities = [
            // ==================================================
            // ESSENTIALS
            // ==================================================

            [
                'slug' => 'wifi',
                'name' => json_encode(['en' => 'Wi-Fi', 'sr' => 'Wi-Fi']),
                'icon' => 'Wifi',
                'category' => 'essential',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => true,
                'is_active' => true,
                'sort_order' => 1
            ],
            [
                'slug' => 'tv',
                'name' => json_encode(['en' => 'TV', 'sr' => 'Televizor']),
                'icon' => 'Tv',
                'category' => 'essential',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 2
            ],
            [
                'slug' => 'smart-tv',
                'name' => json_encode(['en' => 'Smart TV', 'sr' => 'Smart televizor']),
                'icon' => 'TvMinimal',
                'category' => 'essential',
                'is_feeable' => false,
                'is_optional' => true,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 3
            ],
            [
                'slug' => 'air-conditioning',
                'name' => json_encode(['en' => 'Air Conditioning', 'sr' => 'Klima uređaj']),
                'icon' => 'Snowflake',
                'category' => 'essential',
                'is_feeable' => true,
                'is_optional' => false,
                'is_highlighted' => true,
                'is_active' => true,
                'sort_order' => 4
            ],
            [
                'slug' => 'heating',
                'name' => json_encode(['en' => 'Heating', 'sr' => 'Grejanje']),
                'icon' => 'ThermometerSun',
                'category' => 'essential',
                'is_feeable' => true,
                'is_optional' => false,
                'is_highlighted' => true,
                'is_active' => true,
                'sort_order' => 5
            ],
            [
                'slug' => 'extra-pillows-blankets',
                'name' => json_encode(['en' => 'Extra Pillows & Blankets', 'sr' => 'Dodatni jastuci i ćebad']),
                'icon' => 'BedSingle',
                'category' => 'essential',
                'is_feeable' => true,
                'is_optional' => true,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 6
            ],
            [
                'slug' => 'hair-dryer',
                'name' => json_encode(['en' => 'Hair Dryer', 'sr' => 'Fen za kosu']),
                'icon' => 'BadgePlus',
                'category' => 'essential',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 7
            ],
            [
                'slug' => 'iron',
                'name' => json_encode(['en' => 'Iron', 'sr' => 'Pegla']),
                'icon' => 'BadgePlus',
                'category' => 'essential',
                'is_feeable' => false,
                'is_optional' => true,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 8
            ],
            [
                'slug' => 'hangers',
                'name' => json_encode(['en' => 'Hangers', 'sr' => 'Vešalice']),
                'icon' => 'BadgePlus',
                'category' => 'essential',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 9
            ],
            [
                'slug' => 'dedicated-workspace',
                'name' => json_encode(['en' => 'Dedicated Workspace', 'sr' => 'Radni prostor']),
                'icon' => 'LaptopMinimalCheck',
                'category' => 'essential',
                'is_feeable' => true,
                'is_optional' => true,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 10
            ],

            // ==================================================
            // KITCHEN & DINING
            // ==================================================

            [
                'slug' => 'kitchen',
                'name' => json_encode(['en' => 'Kitchen', 'sr' => 'Kuhinja']),
                'icon' => 'ChefHat',
                'category' => 'kitchen',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => true,
                'is_active' => true,
                'sort_order' => 20
            ],
            [
                'slug' => 'refrigerator',
                'name' => json_encode(['en' => 'Refrigerator', 'sr' => 'Frižider']),
                'icon' => 'Refrigerator',
                'category' => 'kitchen',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 21
            ],
            [
                'slug' => 'oven',
                'name' => json_encode(['en' => 'Oven', 'sr' => 'Rerna']),
                'icon' => 'Microwave',
                'category' => 'kitchen',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 22
            ],
            [
                'slug' => 'stove',
                'name' => json_encode(['en' => 'Stove', 'sr' => 'Šporet']),
                'icon' => 'Heater',
                'category' => 'kitchen',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 23
            ],
            [
                'slug' => 'microwave',
                'name' => json_encode(['en' => 'Microwave', 'sr' => 'Mikrotalasna pećnica']),
                'icon' => 'Microwave',
                'category' => 'kitchen',
                'is_feeable' => false,
                'is_optional' => true,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 24
            ],
            [
                'slug' => 'dishwasher',
                'name' => json_encode(['en' => 'Dishwasher', 'sr' => 'Mašina za pranje sudova']),
                'icon' => 'BadgePlus',
                'category' => 'kitchen',
                'is_feeable' => false,
                'is_optional' => true,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 25
            ],
            [
                'slug' => 'coffee-maker',
                'name' => json_encode(['en' => 'Coffee Maker', 'sr' => 'Aparat za kafu']),
                'icon' => 'Coffee',
                'category' => 'kitchen',
                'is_feeable' => false,
                'is_optional' => true,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 26
            ],
            [
                'slug' => 'kettle',
                'name' => json_encode(['en' => 'Kettle', 'sr' => 'Čajnik']),
                'icon' => 'BadgePlus',
                'category' => 'kitchen',
                'is_feeable' => false,
                'is_optional' => true,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 27
            ],
            [
                'slug' => 'toaster',
                'name' => json_encode(['en' => 'Toaster', 'sr' => 'Toster']),
                'icon' => 'BadgePlus',
                'category' => 'kitchen',
                'is_feeable' => false,
                'is_optional' => true,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 27
            ],
            [
                'slug' => 'blender',
                'name' => json_encode(['en' => 'Blender', 'sr' => 'Blender']),
                'icon' => 'BadgePlus',
                'category' => 'kitchen',
                'is_feeable' => false,
                'is_optional' => true,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 27
            ],
            [
                'slug' => 'dishes-and-cutlery',
                'name' => json_encode(['en' => 'Dishes & Cutlery', 'sr' => 'Posuđe i escajg']),
                'icon' => 'Utensils',
                'category' => 'kitchen',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 28
            ],
            [
                'slug' => 'dining-table',
                'name' => json_encode(['en' => 'Dining table', 'sr' => 'Trpezarijski sto']),
                'icon' => 'BadgePlus',
                'category' => 'kitchen',
                'is_feeable' => false,
                'is_optional' => true,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 27
            ],
            [
                'slug' => 'wine-glasses',
                'name' => json_encode(['en' => 'Wine glasses', 'sr' => 'Čaše za vino']),
                'icon' => 'Wine',
                'category' => 'kitchen',
                'is_feeable' => false,
                'is_optional' => true,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 27
            ],

            // ==================================================
            // BEDROOM AND LOUNDRY
            // ==================================================

            [
                'slug' => 'bed-linens',
                'name' => json_encode(['en' => 'Bed linens', 'sr' => 'Posteljina']),
                'icon' => 'Dock',
                'category' => 'bedroom',
                'is_feeable' => false,
                'is_optional' => true,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 27
            ],
            [
                'slug' => 'wardrobe-closet',
                'name' => json_encode(['en' => 'Wardrobe closet', 'sr' => 'Ormar za garderobu']),
                'icon' => 'BadgePlus',
                'category' => 'bedroom',
                'is_feeable' => false,
                'is_optional' => true,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 27
            ],
            [
                'slug' => 'washer',
                'name' => json_encode(['en' => 'Washer', 'sr' => 'Mašina za pranje veša']),
                'icon' => 'WashingMachine',
                'category' => 'bedroom',
                'is_feeable' => false,
                'is_optional' => true,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 27
            ],
            [
                'slug' => 'dryer',
                'name' => json_encode(['en' => 'Dryer', 'sr' => 'Sušač']),
                'icon' => 'BadgePlus',
                'category' => 'bedroom',
                'is_feeable' => false,
                'is_optional' => true,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 27
            ],
            [
                'slug' => 'drying-rack',
                'name' => json_encode(['en' => 'Drying rack', 'sr' => 'Stalak za sušenje']),
                'icon' => 'BadgePlus',
                'category' => 'bedroom',
                'is_feeable' => false,
                'is_optional' => true,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 27
            ],
            [
                'slug' => 'laundry-detergent',
                'name' => json_encode(['en' => 'Laundry detergent', 'sr' => 'Deterdžent za veš']),
                'icon' => 'Bubbles',
                'category' => 'bedroom',
                'is_feeable' => false,
                'is_optional' => true,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 27
            ],

            // ==================================================
            // BATHROOM
            // ==================================================

            [
                'slug' => 'hot-water',
                'name' => json_encode(['en' => 'Hot Water', 'sr' => 'Topla voda']),
                'icon' => 'Thermometer',
                'category' => 'bathroom',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 40
            ],
            [
                'slug' => 'bathtub',
                'name' => json_encode(['en' => 'Bathtub', 'sr' => 'Kada']),
                'icon' => 'Bath',
                'category' => 'bathroom',
                'is_feeable' => false,
                'is_optional' => true,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 41
            ],
            [
                'slug' => 'shower',
                'name' => json_encode(['en' => 'Shower', 'sr' => 'Tuš']),
                'icon' => 'ShowerHead',
                'category' => 'bathroom',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'bidet',
                'name' => json_encode(['en' => 'Bidet', 'sr' => 'Bide']),
                'icon' => 'Toilet',
                'category' => 'bathroom',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'shampoo',
                'name' => json_encode(['en' => 'Shampoo', 'sr' => 'Šampon']),
                'icon' => 'Bubbles',
                'category' => 'bathroom',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'towels',
                'name' => json_encode(['en' => 'Towels', 'sr' => 'Peškiri']),
                'icon' => 'BadgePlus',
                'category' => 'bathroom',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 43
            ],
            [
                'slug' => 'toilet-paper',
                'name' => json_encode(['en' => 'Toilet paper', 'sr' => 'Toaletni papir']),
                'icon' => 'BadgePlus',
                'category' => 'bathroom',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],

            // ==================================================
            // HEATING & COOLING
            // ==================================================

            [
                'slug' => 'fireplace',
                'name' => json_encode(['en' => 'Fireplace', 'sr' => 'Kamin']),
                'icon' => 'FlameKindling',
                'category' => 'heating-cooling',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'portable-fan',
                'name' => json_encode(['en' => 'Portable fan', 'sr' => 'Prenosivi ventilator']),
                'icon' => 'Fan',
                'category' => 'heating-cooling',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],

            // ==================================================
            // FAMILY & CHILDREN
            // ==================================================

            [
                'slug' => 'crib',
                'name' => json_encode(['en' => 'Crib', 'sr' => 'Kolevka']),
                'icon' => 'Bed',
                'category' => 'family-children',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'high-chair',
                'name' => json_encode(['en' => 'High chair', 'sr' => 'Visoka stolica']),
                'icon' => 'BadgePlus',
                'category' => 'family-children',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'baby-bath',
                'name' => json_encode(['en' => 'Baby bath', 'sr' => 'Kupatilo za bebe']),
                'icon' => 'Bath',
                'category' => 'family-children',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'baby-monitor',
                'name' => json_encode(['en' => 'Baby monitor', 'sr' => 'Baby monitor']),
                'icon' => 'MonitorSmartphone',
                'category' => 'family-children',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'stair-gates',
                'name' => json_encode(['en' => 'Stair gates', 'sr' => 'Kapije za stepenice']),
                'icon' => 'DoorClosedLocked',
                'category' => 'family-children',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'toys',
                'name' => json_encode(['en' => 'Toys', 'sr' => 'Igračke']),
                'icon' => 'Blocks',
                'category' => 'family-children',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'children-books',
                'name' => json_encode(['en' => 'Children books', 'sr' => 'Knjige za decu']),
                'icon' => 'Library',
                'category' => 'family-children',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],

            // ==================================================
            // PETS
            // ==================================================

            [
                'slug' => 'pets-allowed',
                'name' => json_encode(['en' => 'Pets Allowed', 'sr' => 'Dozvoljeni kućni ljubimci']),
                'icon' => 'PawPrint',
                'category' => 'pets',
                'is_feeable' => true,
                'is_optional' => true,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 100
            ],
            [
                'slug' => 'pet-bowls',
                'name' => json_encode(['en' => 'Pet bowls', 'sr' => 'Posude za kućne ljubimce']),
                'icon' => 'Bone',
                'category' => 'pets',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'pet-bed',
                'name' => json_encode(['en' => 'Pet bed', 'sr' => 'Krevet za kućne ljubimce']),
                'icon' => 'Bed',
                'category' => 'pets',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],


            // ==================================================
            // OUTDOOR
            // ==================================================

            [
                'slug' => 'pool',
                'name' => json_encode(['en' => 'Pool', 'sr' => 'Bazen']),
                'icon' => 'WavesLadder',
                'category' => 'outdoor',
                'is_feeable' => true,
                'is_optional' => true,
                'is_highlighted' => true,
                'is_active' => true,
                'sort_order' => 80
            ],
            [
                'slug' => 'hot-tub',
                'name' => json_encode(['en' => 'Hot tub', 'sr' => 'Hot tub']),
                'icon' => 'BadgePlus',
                'category' => 'outdoor',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'balcony-patio',
                'name' => json_encode(['en' => 'Balcony/Patio', 'sr' => 'Balkon']),
                'icon' => 'BadgePlus',
                'category' => 'outdoor',
                'is_feeable' => false,
                'is_optional' => true,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 81
            ],
            [
                'slug' => 'backyard',
                'name' => json_encode(['en' => 'Backyard', 'sr' => 'Dvorište']),
                'icon' => 'Birdhouse',
                'category' => 'outdoor',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'bbq-grill',
                'name' => json_encode(['en' => 'BBQ Grill', 'sr' => 'Roštilj']),
                'icon' => 'Hamburger',
                'category' => 'outdoor',
                'is_feeable' => true,
                'is_optional' => true,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 82
            ],
            [
                'slug' => 'outdoor-dining',
                'name' => json_encode(['en' => 'Outdoor dining', 'sr' => 'Trpezarija na otvorenom']),
                'icon' => 'BadgePlus',
                'category' => 'outdoor',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'fire-pit',
                'name' => json_encode(['en' => 'Fire pit', 'sr' => 'Ognjište na otvorenom']),
                'icon' => 'Flame',
                'category' => 'outdoor',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'beach-essentials',
                'name' => json_encode(['en' => 'Beach essentials', 'sr' => 'Oprema za plažu']),
                'icon' => 'Volleyball',
                'category' => 'outdoor',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'sun-loungers',
                'name' => json_encode(['en' => 'Sun loungers', 'sr' => 'Ležaljke']),
                'icon' => 'BadgePlus',
                'category' => 'outdoor',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],

            // ==================================================
            // PARKING & FACILITIES
            // ==================================================

            [
                'slug' => 'free-parking',
                'name' => json_encode(['en' => 'Free Parking', 'sr' => 'Besplatan parking']),
                'icon' => 'CircleParking',
                'category' => 'parking-facilities',
                'is_feeable' => false,
                'is_optional' => true,
                'is_highlighted' => true,
                'is_active' => true,
                'sort_order' => 60
            ],
            // [
            //     'slug' => 'paid-parking',
            //     'name' => json_encode(['en' => 'Paid Parking', 'sr' => 'Parking uz doplatu']),
            //     'icon' => 'SquareParking',
            //     'category' => 'parking-facilities',
            //     'is_feeable' => true,
            //     'is_optional' => true,
            //     'is_highlighted' => false,
            //     'is_active' => true,
            //     'sort_order' => 61
            // ],
            [
                'slug' => 'garage',
                'name' => json_encode(['en' => 'Garage', 'sr' => 'Garaža']),
                'icon' => 'Warehouse',
                'category' => 'parking-facilities',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'elevator',
                'name' => json_encode(['en' => 'Elevator', 'sr' => 'Lift']),
                'icon' => 'BadgePlus',
                'category' => 'parking-facilities',
                'is_feeable' => false,
                'is_optional' => true,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 62
            ],
            [
                'slug' => 'ev-charger',
                'name' => json_encode(['en' => 'EV charger', 'sr' => 'EV punjač']),
                'icon' => 'EvCharger',
                'category' => 'parking-facilities',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'wheelchair-access',
                'name' => json_encode(['en' => 'Wheelchair access', 'sr' => 'Pristup za invalidska kolica']),
                'icon' => 'Accessibility',
                'category' => 'parking-facilities',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'luggage-dropoff',
                'name' => json_encode(['en' => 'Luggage dropoff', 'sr' => 'Odlaganje prtljaga']),
                'icon' => 'Luggage',
                'category' => 'parking-facilities',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],

            // ==================================================
            // SAFETY
            // ==================================================

            [
                'slug' => 'smoke-alarm',
                'name' => json_encode(['en' => 'Smoke Alarm', 'sr' => 'Detektor dima']),
                'icon' => 'AlarmSmoke',
                'category' => 'safety',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 120
            ],
            [
                'slug' => 'co-alarm',
                'name' => json_encode(['en' => 'CO alarm', 'sr' => 'CO alarm']),
                'icon' => 'AlarmSmoke',
                'category' => 'safety',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'fire-extinguisher',
                'name' => json_encode(['en' => 'Fire extinguisher', 'sr' => 'Aparat za gašenje požara']),
                'icon' => 'FireExtinguisher',
                'category' => 'safety',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'first-aid-kit',
                'name' => json_encode(['en' => 'First Aid Kit', 'sr' => 'Prva pomoć']),
                'icon' => 'BriefcaseMedical',
                'category' => 'safety',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 121
            ],
            [
                'slug' => 'security-cameras',
                'name' => json_encode(['en' => 'Security cameras', 'sr' => 'Sigurnosne kamere']),
                'icon' => 'Cctv',
                'category' => 'safety',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'lockbox',
                'name' => json_encode(['en' => 'Lockbox', 'sr' => 'Lockbox']),
                'icon' => 'Vault',
                'category' => 'safety',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'smart-lock',
                'name' => json_encode(['en' => 'Smart lock', 'sr' => 'Smart lock']),
                'icon' => 'Vault',
                'category' => 'safety',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],

            // ==================================================
            // ENTERTAINMENT
            // ==================================================

            [
                'slug' => 'streaming-services',
                'name' => json_encode(['en' => 'Streaming services', 'sr' => 'Usluge strimovanja']),
                'icon' => 'TvMinimalPlay',
                'category' => 'entertainment',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'game-console',
                'name' => json_encode(['en' => 'Game console', 'sr' => 'Gaming konzola']),
                'icon' => 'Gamepad',
                'category' => 'entertainment',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'board-games',
                'name' => json_encode(['en' => 'Board games', 'sr' => 'Društvene igre']),
                'icon' => 'Dices',
                'category' => 'entertainment',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'books',
                'name' => json_encode(['en' => 'Books', 'sr' => 'Knjige']),
                'icon' => 'Book',
                'category' => 'entertainment',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'sound-system',
                'name' => json_encode(['en' => 'Sound system', 'sr' => 'Ozvučenje']),
                'icon' => 'Speaker',
                'category' => 'entertainment',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'piano',
                'name' => json_encode(['en' => 'Piano', 'sr' => 'Klavir']),
                'icon' => 'Piano',
                'category' => 'entertainment',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],

            // ==================================================
            // INTERNET & OFFICE
            // ==================================================

            [
                'slug' => 'ethernet',
                'name' => json_encode(['en' => 'Ethernet', 'sr' => 'Ethernet']),
                'icon' => 'EthernetPort',
                'category' => 'office',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'printer',
                'name' => json_encode(['en' => 'Printer', 'sr' => 'Štampač']),
                'icon' => 'Printer',
                'category' => 'office',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],

            // ==================================================
            // LOCATION FEATURES
            // ==================================================

            [
                'slug' => 'beachfront',
                'name' => json_encode(['en' => 'Beachfront', 'sr' => 'Pogled na plažu']),
                'icon' => 'Volleyball',
                'category' => 'location',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'waterfront',
                'name' => json_encode(['en' => 'Waterfront', 'sr' => 'Promenada']),
                'icon' => 'Dam',
                'category' => 'location',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'ski-in-out',
                'name' => json_encode(['en' => 'Ski in / Ski out', 'sr' => 'Ski-in / ski-out']),
                'icon' => 'CableCar',
                'category' => 'location',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'resort-access',
                'name' => json_encode(['en' => 'Resort access', 'sr' => 'Pristup odmaralištu']),
                'icon' => 'FlagTriangleRight',
                'category' => 'location',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],

            // ==================================================
            // LUXURY
            // ==================================================

            [
                'slug' => 'gym',
                'name' => json_encode(['en' => 'Gym', 'sr' => 'Teretana']),
                'icon' => 'Dumbbell',
                'category' => 'luxury',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'sauna',
                'name' => json_encode(['en' => 'Sauna', 'sr' => 'Sauna']),
                'icon' => 'Bubbles',
                'category' => 'luxury',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'spa',
                'name' => json_encode(['en' => 'Spa', 'sr' => 'Spa']),
                'icon' => 'Bubbles',
                'category' => 'luxury',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'pool-table',
                'name' => json_encode(['en' => 'Pool table', 'sr' => 'Bilijarski sto']),
                'icon' => 'BadgePlus',
                'category' => 'luxury',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'ping-pong',
                'name' => json_encode(['en' => 'Ping pong', 'sr' => 'Ping pong']),
                'icon' => 'BadgePlus',
                'category' => 'luxury',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'home-teather',
                'name' => json_encode(['en' => 'Home teather', 'sr' => 'Kućni bioskop']),
                'icon' => 'Popcorn',
                'category' => 'luxury',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],

            // ==================================================
            // SERVICES
            // ==================================================

            [
                'slug' => 'self-check-in',
                'name' => json_encode(['en' => 'Self check in', 'sr' => 'Samostalna prijava']),
                'icon' => 'UserRoundCheck',
                'category' => 'services',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'keyless-entry',
                'name' => json_encode(['en' => 'Keyless entry', 'sr' => 'Ulaz bez ključa']),
                'icon' => 'BadgePlus',
                'category' => 'services',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'cleaning-service',
                'name' => json_encode(['en' => 'Cleaning service', 'sr' => 'Usluga čišćenja']),
                'icon' => 'BrushCleaning',
                'category' => 'services',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'breakfast',
                'name' => json_encode(['en' => 'Breakfast', 'sr' => 'Doručak']),
                'icon' => 'UtensilsCrossed',
                'category' => 'services',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'long-term-stays',
                'name' => json_encode(['en' => 'Long-term stays', 'sr' => 'Dugoročni boravak']),
                'icon' => 'Scroll',
                'category' => 'services',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
            [
                'slug' => 'host-greeting',
                'name' => json_encode(['en' => 'Host greeting', 'sr' => 'Doček od strane domaćina']),
                'icon' => 'Handshake',
                'category' => 'services',
                'is_feeable' => false,
                'is_optional' => false,
                'is_highlighted' => false,
                'is_active' => true,
                'sort_order' => 42
            ],
        ];

        \DB::table('amenities')->insert($amenities);
    }
};
