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
            $table->json('name'); // Multilingual support
            $table->string('icon')->nullable();
            $table->enum('category', ['property', 'unit', 'both'])->default('both');
            $table->enum('type', [
                'general',
                'kitchen',
                'bathroom',
                'bedroom',
                'entertainment',
                'outdoor',
                'services',
                'safety',
                'accessibility',
                'family',
                'workspace'
            ])->default('general');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['category', 'type']);
            $table->index('is_active');
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
            // ============================================
            // GENERAL AMENITIES
            // ============================================
            [
                'slug' => 'wifi',
                'name' => json_encode(['en' => 'Wi-Fi', 'sr' => 'Wi-Fi']),
                'icon' => 'WiFi',
                'category' => 'both',
                'type' => 'general',
                'sort_order' => 1
            ],
            [
                'slug' => 'air-conditioning',
                'name' => json_encode(['en' => 'Air Conditioning', 'sr' => 'Klima']),
                'icon' => 'Snowflake',
                'category' => 'both',
                'type' => 'general',
                'sort_order' => 2
            ],
            [
                'slug' => 'heating',
                'name' => json_encode(['en' => 'Heating', 'sr' => 'Grejanje']),
                'icon' => 'Heating',
                'category' => 'both',
                'type' => 'general',
                'sort_order' => 3
            ],
            [
                'slug' => 'free-parking',
                'name' => json_encode(['en' => 'Free Parking', 'sr' => 'Besplatan parking']),
                'icon' => 'FreeParking',
                'category' => 'property',
                'type' => 'general',
                'sort_order' => 4
            ],
            [
                'slug' => 'paid-parking',
                'name' => json_encode(['en' => 'Paid Parking', 'sr' => 'Parking uz naplatu']),
                'icon' => 'PaidParking',
                'category' => 'property',
                'type' => 'general',
                'sort_order' => 5
            ],
            [
                'slug' => 'elevator',
                'name' => json_encode(['en' => 'Elevator', 'sr' => 'Lift']),
                'icon' => 'Elevator',
                'category' => 'property',
                'type' => 'general',
                'sort_order' => 6
            ],
            [
                'slug' => 'washer',
                'name' => json_encode(['en' => 'Washer', 'sr' => 'Mašina za pranje veša']),
                'icon' => 'Washer',
                'category' => 'both',
                'type' => 'general',
                'sort_order' => 7
            ],
            [
                'slug' => 'dryer',
                'name' => json_encode(['en' => 'Dryer', 'sr' => 'Sušilica']),
                'icon' => 'Dryer',
                'category' => 'both',
                'type' => 'general',
                'sort_order' => 8
            ],

            // ============================================
            // KITCHEN AMENITIES
            // ============================================
            [
                'slug' => 'kitchen',
                'name' => json_encode(['en' => 'Kitchen', 'sr' => 'Kuhinja']),
                'icon' => 'Kitchen',
                'category' => 'unit',
                'type' => 'kitchen',
                'sort_order' => 10
            ],
            [
                'slug' => 'kitchenette',
                'name' => json_encode(['en' => 'Kitchenette', 'sr' => 'Čajna kuhinja']),
                'icon' => 'Kitchenette',
                'category' => 'unit',
                'type' => 'kitchen',
                'sort_order' => 11
            ],
            [
                'slug' => 'refrigerator',
                'name' => json_encode(['en' => 'Refrigerator', 'sr' => 'Frižider']),
                'icon' => 'Refrigerator',
                'category' => 'unit',
                'type' => 'kitchen',
                'sort_order' => 12
            ],
            [
                'slug' => 'microwave',
                'name' => json_encode(['en' => 'Microwave', 'sr' => 'Mikrotalasna']),
                'icon' => 'Microwave',
                'category' => 'unit',
                'type' => 'kitchen',
                'sort_order' => 13
            ],
            [
                'slug' => 'oven',
                'name' => json_encode(['en' => 'Oven', 'sr' => 'Rerna']),
                'icon' => 'Oven',
                'category' => 'unit',
                'type' => 'kitchen',
                'sort_order' => 14
            ],
            [
                'slug' => 'stove',
                'name' => json_encode(['en' => 'Stove', 'sr' => 'Šporet']),
                'icon' => 'Stove',
                'category' => 'unit',
                'type' => 'kitchen',
                'sort_order' => 15
            ],
            [
                'slug' => 'dishwasher',
                'name' => json_encode(['en' => 'Dishwasher', 'sr' => 'Mašina za suđe']),
                'icon' => 'Dishwasher',
                'category' => 'unit',
                'type' => 'kitchen',
                'sort_order' => 16
            ],
            [
                'slug' => 'coffee-maker',
                'name' => json_encode(['en' => 'Coffee Maker', 'sr' => 'Aparat za kafu']),
                'icon' => 'CoffeeMaker',
                'category' => 'unit',
                'type' => 'kitchen',
                'sort_order' => 17
            ],
            [
                'slug' => 'kettle',
                'name' => json_encode(['en' => 'Electric Kettle', 'sr' => 'Kuvalo za vodu']),
                'icon' => 'Kettle',
                'category' => 'unit',
                'type' => 'kitchen',
                'sort_order' => 18
            ],
            [
                'slug' => 'toaster',
                'name' => json_encode(['en' => 'Toaster', 'sr' => 'Toster']),
                'icon' => 'Toaster',
                'category' => 'unit',
                'type' => 'kitchen',
                'sort_order' => 19
            ],
            [
                'slug' => 'dining-table',
                'name' => json_encode(['en' => 'Dining Table', 'sr' => 'Trpezarijski sto']),
                'icon' => 'DiningTable',
                'category' => 'unit',
                'type' => 'kitchen',
                'sort_order' => 20
            ],
            [
                'slug' => 'cooking-basics',
                'name' => json_encode(['en' => 'Cooking Basics', 'sr' => 'Osnovni kuhinjski pribor']),
                'icon' => 'CookingBasics',
                'category' => 'unit',
                'type' => 'kitchen',
                'sort_order' => 21
            ],
            [
                'slug' => 'dishes-silverware',
                'name' => json_encode(['en' => 'Dishes & Silverware', 'sr' => 'Posuđe i escajg']),
                'icon' => 'DishesSilverware',
                'category' => 'unit',
                'type' => 'kitchen',
                'sort_order' => 22
            ],
            [
                'slug' => 'wine-glasses',
                'name' => json_encode(['en' => 'Wine Glasses', 'sr' => 'Čaše za vino']),
                'icon' => 'WineGlasses',
                'category' => 'unit',
                'type' => 'kitchen',
                'sort_order' => 23
            ],
            [
                'slug' => 'freezer',
                'name' => json_encode(['en' => 'Freezer', 'sr' => 'Zamrzivač']),
                'icon' => 'Freezer',
                'category' => 'unit',
                'type' => 'kitchen',
                'sort_order' => 24
            ],
            [
                'slug' => 'mini-bar',
                'name' => json_encode(['en' => 'Mini-bar', 'sr' => 'Mini bar']),
                'icon' => 'MiniBar',
                'category' => 'unit',
                'type' => 'kitchen',
                'sort_order' => 25
            ],

            // ============================================
            // BATHROOM AMENITIES
            // ============================================
            [
                'slug' => 'private-bathroom',
                'name' => json_encode(['en' => 'Private Bathroom', 'sr' => 'Privatno kupatilo']),
                'icon' => 'PrivateBathroom',
                'category' => 'unit',
                'type' => 'bathroom',
                'sort_order' => 30
            ],
            [
                'slug' => 'shared-bathroom',
                'name' => json_encode(['en' => 'Shared Bathroom', 'sr' => 'Zajedničko kupatilo']),
                'icon' => 'SharedBathroom',
                'category' => 'property',
                'type' => 'bathroom',
                'sort_order' => 31
            ],
            [
                'slug' => 'shower',
                'name' => json_encode(['en' => 'Shower', 'sr' => 'Tuš']),
                'icon' => 'Shower',
                'category' => 'unit',
                'type' => 'bathroom',
                'sort_order' => 32
            ],
            [
                'slug' => 'bathtub',
                'name' => json_encode(['en' => 'Bathtub', 'sr' => 'Kada']),
                'icon' => 'Bathtub',
                'category' => 'unit',
                'type' => 'bathroom',
                'sort_order' => 33
            ],
            [
                'slug' => 'hot-water',
                'name' => json_encode(['en' => 'Hot Water', 'sr' => 'Topla voda']),
                'icon' => 'HotWater',
                'category' => 'both',
                'type' => 'bathroom',
                'sort_order' => 34
            ],
            [
                'slug' => 'hair-dryer',
                'name' => json_encode(['en' => 'Hair Dryer', 'sr' => 'Fen za kosu']),
                'icon' => 'HairDryer',
                'category' => 'unit',
                'type' => 'bathroom',
                'sort_order' => 35
            ],
            [
                'slug' => 'shampoo',
                'name' => json_encode(['en' => 'Shampoo', 'sr' => 'Šampon']),
                'icon' => 'Shampoo',
                'category' => 'unit',
                'type' => 'bathroom',
                'sort_order' => 36
            ],
            [
                'slug' => 'body-soap',
                'name' => json_encode(['en' => 'Body Soap', 'sr' => 'Sapun']),
                'icon' => 'BodySoap',
                'category' => 'unit',
                'type' => 'bathroom',
                'sort_order' => 37
            ],
            [
                'slug' => 'towels',
                'name' => json_encode(['en' => 'Towels', 'sr' => 'Peškiri']),
                'icon' => 'Towels',
                'category' => 'unit',
                'type' => 'bathroom',
                'sort_order' => 38
            ],
            [
                'slug' => 'toilet-paper',
                'name' => json_encode(['en' => 'Toilet Paper', 'sr' => 'Toalet papir']),
                'icon' => 'ToiletPaper',
                'category' => 'unit',
                'type' => 'bathroom',
                'sort_order' => 39
            ],
            [
                'slug' => 'bidet',
                'name' => json_encode(['en' => 'Bidet', 'sr' => 'Bide']),
                'icon' => 'Bidet',
                'category' => 'unit',
                'type' => 'bathroom',
                'sort_order' => 40
            ],

            // ============================================
            // BEDROOM AMENITIES
            // ============================================
            [
                'slug' => 'bed-linens',
                'name' => json_encode(['en' => 'Bed Linens', 'sr' => 'Posteljina']),
                'icon' => 'BedLinens',
                'category' => 'unit',
                'type' => 'bedroom',
                'sort_order' => 45
            ],
            [
                'slug' => 'extra-pillows',
                'name' => json_encode(['en' => 'Extra Pillows & Blankets', 'sr' => 'Dodatni jastuci i ćebad']),
                'icon' => 'ExtraPillows',
                'category' => 'unit',
                'type' => 'bedroom',
                'sort_order' => 46
            ],
            [
                'slug' => 'hangers',
                'name' => json_encode(['en' => 'Hangers', 'sr' => 'Vešalice']),
                'icon' => 'Hangers',
                'category' => 'unit',
                'type' => 'bedroom',
                'sort_order' => 47
            ],
            [
                'slug' => 'iron',
                'name' => json_encode(['en' => 'Iron', 'sr' => 'Pegla']),
                'icon' => 'Iron',
                'category' => 'unit',
                'type' => 'bedroom',
                'sort_order' => 48
            ],
            [
                'slug' => 'iron-board',
                'name' => json_encode(['en' => 'Iron Board', 'sr' => 'Daska za peglanje']),
                'icon' => 'IronBoard',
                'category' => 'unit',
                'type' => 'bedroom',
                'sort_order' => 49
            ],
            [
                'slug' => 'wardrobe',
                'name' => json_encode(['en' => 'Wardrobe/Closet', 'sr' => 'Orman/Garderober']),
                'icon' => 'Wardrobe',
                'category' => 'unit',
                'type' => 'bedroom',
                'sort_order' => 50
            ],
            [
                'slug' => 'blackout-curtains',
                'name' => json_encode(['en' => 'Blackout Curtains', 'sr' => 'Zavese za zamračivanje']),
                'icon' => 'BlackoutCurtains',
                'category' => 'unit',
                'type' => 'bedroom',
                'sort_order' => 51
            ],
            [
                'slug' => 'safe',
                'name' => json_encode(['en' => 'Safe', 'sr' => 'Sef']),
                'icon' => 'Safe',
                'category' => 'unit',
                'type' => 'bedroom',
                'sort_order' => 52
            ],

            // ============================================
            // ENTERTAINMENT AMENITIES
            // ============================================
            [
                'slug' => 'tv',
                'name' => json_encode(['en' => 'TV', 'sr' => 'Televizor']),
                'icon' => 'TV',
                'category' => 'unit',
                'type' => 'entertainment',
                'sort_order' => 55
            ],
            [
                'slug' => 'cable-tv',
                'name' => json_encode(['en' => 'Cable TV', 'sr' => 'Kablovska TV']),
                'icon' => 'CableTV',
                'category' => 'unit',
                'type' => 'entertainment',
                'sort_order' => 56
            ],
            [
                'slug' => 'netflix',
                'name' => json_encode(['en' => 'Netflix', 'sr' => 'Netflix']),
                'icon' => 'Netflix',
                'category' => 'unit',
                'type' => 'entertainment',
                'sort_order' => 57
            ],
            [
                'slug' => 'sound-system',
                'name' => json_encode(['en' => 'Sound System', 'sr' => 'Zvučni sistem']),
                'icon' => 'SoundSystem',
                'category' => 'unit',
                'type' => 'entertainment',
                'sort_order' => 58
            ],
            [
                'slug' => 'board-games',
                'name' => json_encode(['en' => 'Board Games', 'sr' => 'Društvene igre']),
                'icon' => 'BoardGames',
                'category' => 'both',
                'type' => 'entertainment',
                'sort_order' => 59
            ],
            [
                'slug' => 'books',
                'name' => json_encode(['en' => 'Books', 'sr' => 'Knjige']),
                'icon' => 'Books',
                'category' => 'unit',
                'type' => 'entertainment',
                'sort_order' => 60
            ],

            // ============================================
            // OUTDOOR AMENITIES
            // ============================================
            [
                'slug' => 'pool',
                'name' => json_encode(['en' => 'Pool', 'sr' => 'Bazen']),
                'icon' => 'Pool',
                'category' => 'property',
                'type' => 'outdoor',
                'sort_order' => 65
            ],
            [
                'slug' => 'hot-tub',
                'name' => json_encode(['en' => 'Hot Tub', 'sr' => 'Hidromasažna kada']),
                'icon' => 'HotTub',
                'category' => 'both',
                'type' => 'outdoor',
                'sort_order' => 66
            ],
            [
                'slug' => 'sauna',
                'name' => json_encode(['en' => 'Sauna', 'sr' => 'Sauna']),
                'icon' => 'Sauna',
                'category' => 'property',
                'type' => 'outdoor',
                'sort_order' => 67
            ],
            [
                'slug' => 'garden',
                'name' => json_encode(['en' => 'Garden', 'sr' => 'Bašta']),
                'icon' => 'Garden',
                'category' => 'property',
                'type' => 'outdoor',
                'sort_order' => 68
            ],
            [
                'slug' => 'balcony',
                'name' => json_encode(['en' => 'Balcony', 'sr' => 'Balkon']),
                'icon' => 'Balcony',
                'category' => 'unit',
                'type' => 'outdoor',
                'sort_order' => 69
            ],
            [
                'slug' => 'terrace',
                'name' => json_encode(['en' => 'Terrace', 'sr' => 'Terasa']),
                'icon' => 'Terrace',
                'category' => 'both',
                'type' => 'outdoor',
                'sort_order' => 70
            ],
            [
                'slug' => 'patio',
                'name' => json_encode(['en' => 'Patio', 'sr' => 'Dvorište']),
                'icon' => 'Patio',
                'category' => 'property',
                'type' => 'outdoor',
                'sort_order' => 71
            ],
            [
                'slug' => 'bbq-grill',
                'name' => json_encode(['en' => 'BBQ Grill', 'sr' => 'Roštilj']),
                'icon' => 'BBQGrill',
                'category' => 'property',
                'type' => 'outdoor',
                'sort_order' => 72
            ],
            [
                'slug' => 'outdoor-furniture',
                'name' => json_encode(['en' => 'Outdoor Furniture', 'sr' => 'Baštenska garnitura']),
                'icon' => 'OutdoorFurniture',
                'category' => 'property',
                'type' => 'outdoor',
                'sort_order' => 73
            ],
            [
                'slug' => 'beach-access',
                'name' => json_encode(['en' => 'Beach Access', 'sr' => 'Pristup plaži']),
                'icon' => 'BeachAccess',
                'category' => 'property',
                'type' => 'outdoor',
                'sort_order' => 74
            ],
            [
                'slug' => 'waterfront',
                'name' => json_encode(['en' => 'Waterfront', 'sr' => 'Na obali']),
                'icon' => 'Waterfront',
                'category' => 'property',
                'type' => 'outdoor',
                'sort_order' => 75
            ],
            [
                'slug' => 'lake-access',
                'name' => json_encode(['en' => 'Lake Access', 'sr' => 'Pristup jezeru']),
                'icon' => 'LakeAccess',
                'category' => 'property',
                'type' => 'outdoor',
                'sort_order' => 76
            ],
            [
                'slug' => 'ski-in-ski-out',
                'name' => json_encode(['en' => 'Ski-in/Ski-out', 'sr' => 'Ski-in/Ski-out']),
                'icon' => 'SkiInSkiOut',
                'category' => 'property',
                'type' => 'outdoor',
                'sort_order' => 77
            ],

            // ============================================
            // SERVICES AMENITIES
            // ============================================
            [
                'slug' => 'breakfast-included',
                'name' => json_encode(['en' => 'Breakfast Included', 'sr' => 'Doručak uključen']),
                'icon' => 'BreakfastIncluded',
                'category' => 'property',
                'type' => 'services',
                'sort_order' => 80
            ],
            [
                'slug' => 'gym',
                'name' => json_encode(['en' => 'Gym/Fitness Center', 'sr' => 'Teretana']),
                'icon' => 'Gym',
                'category' => 'property',
                'type' => 'services',
                'sort_order' => 81
            ],
            [
                'slug' => 'spa',
                'name' => json_encode(['en' => 'Spa', 'sr' => 'Spa centar']),
                'icon' => 'Spa',
                'category' => 'property',
                'type' => 'services',
                'sort_order' => 82
            ],
            [
                'slug' => 'restaurant',
                'name' => json_encode(['en' => 'Restaurant', 'sr' => 'Restoran']),
                'icon' => 'Restaurant',
                'category' => 'property',
                'type' => 'services',
                'sort_order' => 83
            ],
            [
                'slug' => 'bar',
                'name' => json_encode(['en' => 'Bar', 'sr' => 'Bar']),
                'icon' => 'Bar',
                'category' => 'property',
                'type' => 'services',
                'sort_order' => 84
            ],
            [
                'slug' => 'room-service',
                'name' => json_encode(['en' => 'Room Service', 'sr' => 'Usluga u sobu']),
                'icon' => 'RoomService',
                'category' => 'property',
                'type' => 'services',
                'sort_order' => 85
            ],
            [
                'slug' => 'concierge',
                'name' => json_encode(['en' => 'Concierge', 'sr' => 'Concierge']),
                'icon' => 'Concierge',
                'category' => 'property',
                'type' => 'services',
                'sort_order' => 86
            ],
            [
                'slug' => 'laundry-service',
                'name' => json_encode(['en' => 'Laundry Service', 'sr' => 'Usluga pranja veša']),
                'icon' => 'LaundryService',
                'category' => 'property',
                'type' => 'services',
                'sort_order' => 87
            ],
            [
                'slug' => 'luggage-storage',
                'name' => json_encode(['en' => 'Luggage Storage', 'sr' => 'Čuvanje prtljaga']),
                'icon' => 'LuggageStorage',
                'category' => 'property',
                'type' => 'services',
                'sort_order' => 88
            ],
            [
                'slug' => 'cleaning-service',
                'name' => json_encode(['en' => 'Cleaning Service', 'sr' => 'Usluga čišćenja']),
                'icon' => 'CleaningService',
                'category' => 'property',
                'type' => 'services',
                'sort_order' => 89
            ],
            [
                'slug' => '24-hour-reception',
                'name' => json_encode(['en' => '24-hour Reception', 'sr' => 'Recepcija 24h']),
                'icon' => '24HourReception',
                'category' => 'property',
                'type' => 'services',
                'sort_order' => 90
            ],
            [
                'slug' => 'airport-shuttle',
                'name' => json_encode(['en' => 'Airport Shuttle', 'sr' => 'Shuttle do aerodroma']),
                'icon' => 'AirportShuttle',
                'category' => 'property',
                'type' => 'services',
                'sort_order' => 91
            ],

            // ============================================
            // SAFETY AMENITIES
            // ============================================
            [
                'slug' => 'smoke-alarm',
                'name' => json_encode(['en' => 'Smoke Alarm', 'sr' => 'Detektor dima']),
                'icon' => 'SmokeAlarm',
                'category' => 'both',
                'type' => 'safety',
                'sort_order' => 95
            ],
            [
                'slug' => 'carbon-monoxide-alarm',
                'name' => json_encode(['en' => 'Carbon Monoxide Alarm', 'sr' => 'Detektor ugljen-monoksida']),
                'icon' => 'CarbonMonoxideAlarm',
                'category' => 'both',
                'type' => 'safety',
                'sort_order' => 96
            ],
            [
                'slug' => 'fire-extinguisher',
                'name' => json_encode(['en' => 'Fire Extinguisher', 'sr' => 'Aparat za gašenje požara']),
                'icon' => 'FireExtinguisher',
                'category' => 'both',
                'type' => 'safety',
                'sort_order' => 97
            ],
            [
                'slug' => 'first-aid-kit',
                'name' => json_encode(['en' => 'First Aid Kit', 'sr' => 'Kutija prve pomoći']),
                'icon' => 'FirstAidKit',
                'category' => 'both',
                'type' => 'safety',
                'sort_order' => 98
            ],
            [
                'slug' => 'security-cameras',
                'name' => json_encode(['en' => 'Security Cameras on Property', 'sr' => 'Sigurnosne kamere']),
                'icon' => 'SecurityCameras',
                'category' => 'property',
                'type' => 'safety',
                'sort_order' => 99
            ],
            [
                'slug' => 'keypad-lock',
                'name' => json_encode(['en' => 'Keypad Lock', 'sr' => 'Elektronska brava']),
                'icon' => 'KeypadLock',
                'category' => 'unit',
                'type' => 'safety',
                'sort_order' => 100
            ],

            // ============================================
            // ACCESSIBILITY AMENITIES
            // ============================================
            [
                'slug' => 'wheelchair-accessible',
                'name' => json_encode(['en' => 'Wheelchair Accessible', 'sr' => 'Pristupačno za invalidska kolica']),
                'icon' => 'WheelchairAccessible',
                'category' => 'property',
                'type' => 'accessibility',
                'sort_order' => 105
            ],
            [
                'slug' => 'step-free-access',
                'name' => json_encode(['en' => 'Step-free Access', 'sr' => 'Pristup bez stepenica']),
                'icon' => 'StepFreeAccess',
                'category' => 'property',
                'type' => 'accessibility',
                'sort_order' => 106
            ],
            [
                'slug' => 'wide-doorways',
                'name' => json_encode(['en' => 'Wide Doorways', 'sr' => 'Široka vrata']),
                'icon' => 'WideDoorways',
                'category' => 'unit',
                'type' => 'accessibility',
                'sort_order' => 107
            ],
            [
                'slug' => 'accessible-bathroom',
                'name' => json_encode(['en' => 'Accessible Bathroom', 'sr' => 'Pristupačno kupatilo']),
                'icon' => 'AccessibleBathroom',
                'category' => 'unit',
                'type' => 'accessibility',
                'sort_order' => 108
            ],

            // ============================================
            // FAMILY AMENITIES
            // ============================================
            [
                'slug' => 'pets-allowed',
                'name' => json_encode(['en' => 'Pets Allowed', 'sr' => 'Dozvoljeni kućni ljubimci']),
                'icon' => 'PetsAllowed',
                'category' => 'property',
                'type' => 'family',
                'sort_order' => 110
            ],
            [
                'slug' => 'children-welcome',
                'name' => json_encode(['en' => 'Children Welcome', 'sr' => 'Dobrodošla deca']),
                'icon' => 'ChildrenWelcome',
                'category' => 'property',
                'type' => 'family',
                'sort_order' => 111
            ],
            [
                'slug' => 'baby-cot',
                'name' => json_encode(['en' => 'Baby Cot', 'sr' => 'Krevetac za bebe']),
                'icon' => 'BabyCot',
                'category' => 'unit',
                'type' => 'family',
                'sort_order' => 112
            ],
            [
                'slug' => 'high-chair',
                'name' => json_encode(['en' => 'High Chair', 'sr' => 'Stolica za hranjenje']),
                'icon' => 'HighChair',
                'category' => 'unit',
                'type' => 'family',
                'sort_order' => 113
            ],
            [
                'slug' => 'childrens-books-toys',
                'name' => json_encode(['en' => "Children's Books & Toys", 'sr' => 'Dečije knjige i igračke']),
                'icon' => 'ChildrensBooksToys',
                'category' => 'unit',
                'type' => 'family',
                'sort_order' => 114
            ],
            [
                'slug' => 'baby-safety-gates',
                'name' => json_encode(['en' => 'Baby Safety Gates', 'sr' => 'Sigurnosne ograde za bebe']),
                'icon' => 'BabySafetyGates',
                'category' => 'unit',
                'type' => 'family',
                'sort_order' => 115
            ],
            [
                'slug' => 'playground',
                'name' => json_encode(['en' => 'Playground', 'sr' => 'Igralište']),
                'icon' => 'Playground',
                'category' => 'property',
                'type' => 'family',
                'sort_order' => 116
            ],

            // ============================================
            // WORKSPACE AMENITIES
            // ============================================
            [
                'slug' => 'dedicated-workspace',
                'name' => json_encode(['en' => 'Dedicated Workspace', 'sr' => 'Radni prostor']),
                'icon' => 'DedicatedWorkspace',
                'category' => 'unit',
                'type' => 'workspace',
                'sort_order' => 120
            ],
            [
                'slug' => 'desk',
                'name' => json_encode(['en' => 'Desk', 'sr' => 'Radni sto']),
                'icon' => 'Desk',
                'category' => 'unit',
                'type' => 'workspace',
                'sort_order' => 121
            ],
            [
                'slug' => 'office-chair',
                'name' => json_encode(['en' => 'Office Chair', 'sr' => 'Kancelarijska stolica']),
                'icon' => 'OfficeChair',
                'category' => 'unit',
                'type' => 'workspace',
                'sort_order' => 122
            ],
            [
                'slug' => 'printer',
                'name' => json_encode(['en' => 'Printer', 'sr' => 'Štampač']),
                'icon' => 'Printer',
                'category' => 'unit',
                'type' => 'workspace',
                'sort_order' => 123
            ],
            [
                'slug' => 'ethernet-connection',
                'name' => json_encode(['en' => 'Ethernet Connection', 'sr' => 'Ethernet konekcija']),
                'icon' => 'EthernetConnection',
                'category' => 'unit',
                'type' => 'workspace',
                'sort_order' => 124
            ],

            // ============================================
            // ADDITIONAL POPULAR AMENITIES
            // ============================================
            [
                'slug' => 'long-term-stays-allowed',
                'name' => json_encode(['en' => 'Long-term Stays Allowed', 'sr' => 'Dozvoljeni duži boravci']),
                'icon' => 'LongTermStaysAllowed',
                'category' => 'property',
                'type' => 'general',
                'sort_order' => 130
            ],
            [
                'slug' => 'self-check-in',
                'name' => json_encode(['en' => 'Self Check-in', 'sr' => 'Samostalan check-in']),
                'icon' => 'SelfCheckIn',
                'category' => 'property',
                'type' => 'services',
                'sort_order' => 131
            ],
            [
                'slug' => 'lockbox',
                'name' => json_encode(['en' => 'Lockbox', 'sr' => 'Sigurnosna kutija za ključeve']),
                'icon' => 'Lockbox',
                'category' => 'property',
                'type' => 'services',
                'sort_order' => 132
            ],
            [
                'slug' => 'doorman',
                'name' => json_encode(['en' => 'Doorman', 'sr' => 'Portir']),
                'icon' => 'Doorman',
                'category' => 'property',
                'type' => 'services',
                'sort_order' => 133
            ],
            [
                'slug' => 'ev-charger',
                'name' => json_encode(['en' => 'EV Charger', 'sr' => 'Punjač za električna vozila']),
                'icon' => 'EVCharger',
                'category' => 'property',
                'type' => 'general',
                'sort_order' => 134
            ],
            [
                'slug' => 'bike-storage',
                'name' => json_encode(['en' => 'Bike Storage', 'sr' => 'Prostor za bicikle']),
                'icon' => 'BikeStorage',
                'category' => 'property',
                'type' => 'general',
                'sort_order' => 135
            ],
            [
                'slug' => 'piano',
                'name' => json_encode(['en' => 'Piano', 'sr' => 'Klavir']),
                'icon' => 'Piano',
                'category' => 'unit',
                'type' => 'entertainment',
                'sort_order' => 136
            ],
            [
                'slug' => 'fireplace',
                'name' => json_encode(['en' => 'Fireplace', 'sr' => 'Kamin']),
                'icon' => 'Fireplace',
                'category' => 'unit',
                'type' => 'general',
                'sort_order' => 137
            ],
            [
                'slug' => 'ceiling-fan',
                'name' => json_encode(['en' => 'Ceiling Fan', 'sr' => 'Plafon ventilator']),
                'icon' => 'CeilingFan',
                'category' => 'unit',
                'type' => 'general',
                'sort_order' => 138
            ],
            [
                'slug' => 'mosquito-net',
                'name' => json_encode(['en' => 'Mosquito Net', 'sr' => 'Komarnična mreža']),
                'icon' => 'MosquitoNet',
                'category' => 'unit',
                'type' => 'general',
                'sort_order' => 139
            ],
            [
                'slug' => 'lake-view',
                'name' => json_encode(['en' => 'Lake View', 'sr' => 'Pogled na jezero']),
                'icon' => 'LakeView',
                'category' => 'unit',
                'type' => 'outdoor',
                'sort_order' => 140
            ],
            [
                'slug' => 'mountain-view',
                'name' => json_encode(['en' => 'Mountain View', 'sr' => 'Pogled na planine']),
                'icon' => 'MountainView',
                'category' => 'unit',
                'type' => 'outdoor',
                'sort_order' => 141
            ],
            [
                'slug' => 'sea-view',
                'name' => json_encode(['en' => 'Sea View', 'sr' => 'Pogled na more']),
                'icon' => 'SeaView',
                'category' => 'unit',
                'type' => 'outdoor',
                'sort_order' => 142
            ],
            [
                'slug' => 'city-view',
                'name' => json_encode(['en' => 'City View', 'sr' => 'Pogled na grad']),
                'icon' => 'CityView',
                'category' => 'unit',
                'type' => 'outdoor',
                'sort_order' => 143
            ],
            [
                'slug' => 'garden-view',
                'name' => json_encode(['en' => 'Garden View', 'sr' => 'Pogled na baštu']),
                'icon' => 'GardenView',
                'category' => 'unit',
                'type' => 'outdoor',
                'sort_order' => 144
            ],
            [
                'slug' => 'smoking-allowed',
                'name' => json_encode(['en' => 'Smoking Allowed', 'sr' => 'Dozvoljeno pušenje']),
                'icon' => 'SmokingAllowed',
                'category' => 'property',
                'type' => 'general',
                'sort_order' => 145
            ],
            [
                'slug' => 'non-smoking',
                'name' => json_encode(['en' => 'Non-smoking', 'sr' => 'Zabranjeno pušenje']),
                'icon' => 'NonSmoking',
                'category' => 'property',
                'type' => 'general',
                'sort_order' => 146
            ],
            [
                'slug' => 'parties-events-allowed',
                'name' => json_encode(['en' => 'Parties & Events Allowed', 'sr' => 'Dozvoljene žurke i događaji']),
                'icon' => 'PartiesEventsAllowed',
                'category' => 'property',
                'type' => 'general',
                'sort_order' => 147
            ],
        ];

        \DB::table('amenities')->insert($amenities);
    }
};
