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
        Schema::create('accommodation_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->jsonb('name');
            $table->jsonb('description')->nullable();
            $table->string('icon', 50)->nullable();
            $table->integer('order')->default(999);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        \DB::table('accommodation_types')->insert([
            ['slug' => 'apartment', 'name' => json_encode(['en' => 'Apartment']), 'description' => json_encode(['en' => 'A private, self-contained unit within a building, featuring its own kitchen, bathroom, and living area—ideal for independent stays.']), 'icon' => 'Apartment', 'order' => 1, 'is_active' => true],
            ['slug' => 'house', 'name' => json_encode(['en' => 'House']), 'description' => json_encode(['en' => 'A detached residential property offering full privacy and exclusive use of all rooms and outdoor areas.']), 'icon' => 'House', 'order' => 2, 'is_active' => true],
            ['slug' => 'villa', 'name' => json_encode(['en' => 'Villa']), 'description' => json_encode(['en' => 'A spacious and upscale property, often with a private garden, terrace, or pool—designed for luxury stays.']), 'icon' => 'Villa', 'order' => 3, 'is_active' => true],
            ['slug' => 'condo', 'name' => json_encode(['en' => 'Condo']), 'description' => json_encode(['en' => 'A unit within a residential complex where facilities such as pools, gyms, or shared spaces are often available.']), 'icon' => 'Condo', 'order' => 4, 'is_active' => true],
            ['slug' => 'hotel', 'name' => json_encode(['en' => 'Hotel']), 'description' => json_encode(['en' => 'A professionally managed property offering private rooms, services, and amenities such as reception, housekeeping, and often breakfast.']), 'icon' => 'Apartment', 'order' => 5, 'is_active' => true],
            ['slug' => 'cottage', 'name' => json_encode(['en' => 'Cottage']), 'description' => json_encode(['en' => 'A small, cozy house, usually in a rural or semi-rural location, perfect for relaxed getaways.']), 'icon' => 'Cottage', 'order' => 6, 'is_active' => true],
            ['slug' => 'cabin', 'name' => json_encode(['en' => 'Cabin']), 'description' => json_encode(['en' => 'A rustic, often wooden property, typically located in nature, ideal for outdoor enthusiasts and retreats.']), 'icon' => 'Cabin', 'order' => 7, 'is_active' => true],
            ['slug' => 'studio', 'name' => json_encode(['en' => 'Studio']), 'description' => json_encode(['en' => 'A single-room unit combining living, sleeping, and kitchen areas, suitable for short stays or solo travelers.']), 'icon' => 'Studio', 'order' => 8, 'is_active' => true],
            ['slug' => 'bed-breakfast', 'name' => json_encode(['en' => 'Bed & breakfast']), 'description' => json_encode(['en' => 'A cozy accommodation offering private rooms and breakfast, with a personal, homely touch.']), 'icon' => 'BedBreakfast', 'order' => 9, 'is_active' => true],
            ['slug' => 'townhouse', 'name' => json_encode(['en' => 'Townhouse']), 'description' => json_encode(['en' => 'A multi-story residence sharing walls with neighboring units, combining private living with a community feel.']), 'icon' => 'Townhouse', 'order' => 10, 'is_active' => true],
            ['slug' => 'chalet', 'name' => json_encode(['en' => 'Chalet']), 'description' => json_encode(['en' => 'A wooden mountain house, typically in ski resorts or alpine areas, offering a cozy retreat.']), 'icon' => 'Chalet', 'order' => 11, 'is_active' => true],
            ['slug' => 'bungalow', 'name' => json_encode(['en' => 'Bungalow']), 'description' => json_encode(['en' => 'A single-story house, often with a veranda or garden, providing easy access and comfort.']), 'icon' => 'Bungalow', 'order' => 12, 'is_active' => true],
            ['slug' => 'guesthouse', 'name' => json_encode(['en' => 'Guest house']), 'description' => json_encode(['en' => 'A small residential property offering private rooms and sometimes meals, with a homely atmosphere.']), 'icon' => 'Guesthouse', 'order' => 13, 'is_active' => true],
            ['slug' => 'loft', 'name' => json_encode(['en' => 'Loft']), 'description' => json_encode(['en' => 'A converted industrial space with open floor plans, high ceilings, and modern design.']), 'icon' => 'Loft', 'order' => 14, 'is_active' => true],
            ['slug' => 'penthouse', 'name' => json_encode(['en' => 'Penthouse']), 'description' => json_encode(['en' => 'A luxurious apartment on the top floor, often featuring panoramic views and premium amenities.']), 'icon' => 'Penthouse', 'order' => 15, 'is_active' => true],
            ['slug' => 'resort', 'name' => json_encode(['en' => 'Resort']), 'description' => json_encode(['en' => 'A large property offering extensive amenities like pools, restaurants, sports facilities, and entertainment for leisure stays.']), 'icon' => 'Resort', 'order' => 16, 'is_active' => true],
            ['slug' => 'guest-suite', 'name' => json_encode(['en' => 'Guest suite']), 'description' => json_encode(['en' => 'A private, self-contained unit within a property, offering privacy with easy access to main facilities.']), 'icon' => 'Guestsuite', 'order' => 17, 'is_active' => true],
            ['slug' => 'hostel', 'name' => json_encode(['en' => 'Hostel']), 'description' => json_encode(['en' => 'Budget-friendly accommodation with shared rooms or dormitories, ideal for travelers seeking social experiences.']), 'icon' => 'Apartment', 'order' => 18, 'is_active' => true],
            ['slug' => 'farm-stay', 'name' => json_encode(['en' => 'Farm stay']), 'description' => json_encode(['en' => 'A rural accommodation experience on a working farm, often including interaction with animals or farm activities.']), 'icon' => 'Barn', 'order' => 19, 'is_active' => true],
            ['slug' => 'barn', 'name' => json_encode(['en' => 'Barn']), 'description' => json_encode(['en' => 'A converted agricultural building offering rustic charm with modern comforts.']), 'icon' => 'Barn', 'order' => 20, 'is_active' => true],
            ['slug' => 'treehouse', 'name' => json_encode(['en' => 'Treehouse']), 'description' => json_encode(['en' => 'An elevated accommodation built in or around trees, offering unique nature-immersed experiences.']), 'icon' => 'Tree', 'order' => 21, 'is_active' => true],
            ['slug' => 'houseboat', 'name' => json_encode(['en' => 'Houseboat']), 'description' => json_encode(['en' => 'A floating home offering waterfront living with unique views and tranquil surroundings.']), 'icon' => 'Boat', 'order' => 22, 'is_active' => true],
            ['slug' => 'boat', 'name' => json_encode(['en' => 'Boat']), 'description' => json_encode(['en' => 'A vessel equipped for overnight stays, ranging from sailboats to motor yachts.']), 'icon' => 'Boat', 'order' => 23, 'is_active' => true],
            ['slug' => 'camper', 'name' => json_encode(['en' => 'Camper/RV']), 'description' => json_encode(['en' => 'A mobile accommodation unit with sleeping, cooking, and bathroom facilities for road trips.']), 'icon' => 'Camper/RV', 'order' => 24, 'is_active' => true],
            ['slug' => 'tiny-house', 'name' => json_encode(['en' => 'Tiny house']), 'description' => json_encode(['en' => 'A compact, efficiently designed home offering minimalist living in a small footprint.']), 'icon' => 'Tiny house', 'order' => 25, 'is_active' => true],
            ['slug' => 'glamping', 'name' => json_encode(['en' => 'Glamping']), 'description' => json_encode(['en' => 'Glamorous camping with comfortable amenities like beds, electricity, and stylish furnishings.']), 'icon' => 'Glamping', 'order' => 26, 'is_active' => true],
            ['slug' => 'castle', 'name' => json_encode(['en' => 'Castle']), 'description' => json_encode(['en' => 'A historic fortified structure offering regal accommodations with heritage charm.']), 'icon' => 'Castle', 'order' => 27, 'is_active' => true],
            ['slug' => 'yacht', 'name' => json_encode(['en' => 'Yacht']), 'description' => json_encode(['en' => 'A luxury vessel for exclusive waterborne stays with premium amenities and services.']), 'icon' => 'Boat', 'order' => 28, 'is_active' => true],
            ['slug' => 'dome', 'name' => json_encode(['en' => 'Dome']), 'description' => json_encode(['en' => 'A geodesic or eco-dome structure offering unique architectural experiences.']), 'icon' => 'Dome', 'order' => 29, 'is_active' => true],
            ['slug' => 'tent', 'name' => json_encode(['en' => 'Tent']), 'description' => json_encode(['en' => 'Traditional camping accommodation, offering an authentic outdoor experience.']), 'icon' => 'Tent', 'order' => 30, 'is_active' => true],
            ['slug' => 'yurt', 'name' => json_encode(['en' => 'Yurt']), 'description' => json_encode(['en' => 'A circular tent-like structure with a wooden frame, offering nomadic-inspired comfort.']), 'icon' => 'Yurt', 'order' => 31, 'is_active' => true],
            ['slug' => 'container', 'name' => json_encode(['en' => 'Container']), 'description' => json_encode(['en' => 'A repurposed shipping container transformed into modern, eco-friendly accommodation.']), 'icon' => 'Container', 'order' => 32, 'is_active' => true],
            ['slug' => 'cave', 'name' => json_encode(['en' => 'Cave']), 'description' => json_encode(['en' => 'A natural or carved cave dwelling offering unique underground accommodation.']), 'icon' => 'Mountain', 'order' => 33, 'is_active' => true],
            ['slug' => 'lighthouse', 'name' => json_encode(['en' => 'Lighthouse']), 'description' => json_encode(['en' => 'A converted navigation tower offering coastal views and maritime heritage.']), 'icon' => 'Lighthouse', 'order' => 34, 'is_active' => true],
            ['slug' => 'windmill', 'name' => json_encode(['en' => 'Windmill']), 'description' => json_encode(['en' => 'A converted mill structure offering historic charm with rural countryside views.']), 'icon' => 'Windmill', 'order' => 35, 'is_active' => true],
            ['slug' => 'earth-house', 'name' => json_encode(['en' => 'Earth house']), 'description' => json_encode(['en' => 'A partially underground dwelling built into hillsides, offering sustainable living.']), 'icon' => 'Earth house', 'order' => 36, 'is_active' => true],
            ['slug' => 'cycladic', 'name' => json_encode(['en' => 'Cycladic home']), 'description' => json_encode(['en' => 'A traditional whitewashed Greek island home with blue accents and cubic architecture.']), 'icon' => 'Cycladic home', 'order' => 37, 'is_active' => true],
            ['slug' => 'trullo', 'name' => json_encode(['en' => 'Trullo']), 'description' => json_encode(['en' => 'A traditional stone hut from Puglia, Italy, with a distinctive conical roof.']), 'icon' => 'Trullo', 'order' => 38, 'is_active' => true],
            ['slug' => 'riad', 'name' => json_encode(['en' => 'Riad']), 'description' => json_encode(['en' => 'A traditional Moroccan house with an interior courtyard or garden.']), 'icon' => 'Riad', 'order' => 39, 'is_active' => true],
            ['slug' => 'ryokan', 'name' => json_encode(['en' => 'Ryokan']), 'description' => json_encode(['en' => 'A traditional Japanese inn featuring tatami floors, futon beds, and communal baths.']), 'icon' => 'Ryokan', 'order' => 40, 'is_active' => true],
            ['slug' => 'shepherd-hut', 'name' => json_encode(['en' => 'Shepherd\'s hut']), 'description' => json_encode(['en' => 'A small mobile dwelling traditionally used by shepherds, now a cozy rural retreat.']), 'icon' => 'ShepherdsHut', 'order' => 42, 'is_active' => true],
            ['slug' => 'igloo', 'name' => json_encode(['en' => 'Igloo']), 'description' => json_encode(['en' => 'An ice or snow shelter offering unique Arctic accommodation experiences.']), 'icon' => 'Igloo', 'order' => 43, 'is_active' => true],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accommodation_types');
    }
};
