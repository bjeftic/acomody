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
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        \DB::table('accommodation_types')->insert([
            ['slug' => 'apartment', 'name' => json_encode(['en' => 'Apartment']), 'description' => json_encode(['en' => 'A private, self-contained unit within a building, featuring its own kitchen, bathroom, and living area—ideal for independent stays.']), 'is_active' => true],
            ['slug' => 'house', 'name' => json_encode(['en' => 'House']), 'description' => json_encode(['en' => 'A detached residential property offering full privacy and exclusive use of all rooms and outdoor areas.']), 'is_active' => true],
            ['slug' => 'villa', 'name' => json_encode(['en' => 'Villa']), 'description' => json_encode(['en' => 'A spacious and upscale property, often with a private garden, terrace, or pool—designed for luxury stays.']), 'is_active' => true],
            ['slug' => 'studio', 'name' => json_encode(['en' => 'Studio']), 'description' => json_encode(['en' => 'A single-room unit combining living, sleeping, and kitchen areas, suitable for short stays or solo travelers.']), 'is_active' => true],
            ['slug' => 'hotel', 'name' => json_encode(['en' => 'Hotel']), 'description' => json_encode(['en' => 'A professionally managed property offering private rooms, services, and amenities such as reception, housekeeping, and often breakfast.']), 'is_active' => true],
            ['slug' => 'townhouse', 'name' => json_encode(['en' => 'Townhouse']), 'description' => json_encode(['en' => 'A multi-story residence sharing walls with neighboring units, combining private living with a community feel.']), 'is_active' => true],
            ['slug' => 'condominium', 'name' => json_encode(['en' => 'Condominium']), 'description' => json_encode(['en' => 'A unit within a residential complex where facilities such as pools, gyms, or shared spaces are often available.']), 'is_active' => true],
            ['slug' => 'bungalow', 'name' => json_encode(['en' => 'Bungalow']), 'description' => json_encode(['en' => 'A single-story house, often with a veranda or garden, providing easy access and comfort.']), 'is_active' => true],
            ['slug' => 'guesthouse', 'name' => json_encode(['en' => 'Guesthouse']), 'description' => json_encode(['en' => 'A small residential property offering private rooms and sometimes meals, with a homely atmosphere.']), 'is_active' => true],
            ['slug' => 'resort', 'name' => json_encode(['en' => 'Resort']), 'description' => json_encode(['en' => 'A large property offering extensive amenities like pools, restaurants, sports facilities, and entertainment for leisure stays.']), 'is_active' => true],
            ['slug' => 'hostel', 'name' => json_encode(['en' => 'Hostel']), 'description' => json_encode(['en' => 'Budget-friendly accommodation with shared rooms or dormitories, ideal for travelers seeking social experiences.']), 'is_active' => true],
            ['slug' => 'cabin', 'name' => json_encode(['en' => 'Cabin']), 'description' => json_encode(['en' => 'A rustic, often wooden property, typically located in nature, ideal for outdoor enthusiasts and retreats.']), 'is_active' => true],
            ['slug' => 'cottage', 'name' => json_encode(['en' => 'Cottage']), 'description' => json_encode(['en' => 'A small, cozy house, usually in a rural or semi-rural location, perfect for relaxed getaways.']), 'is_active' => true],
            ['slug' => 'farm-stay', 'name' => json_encode(['en' => 'Farm stay']), 'description' => json_encode(['en' => 'A rural accommodation experience on a working farm, often including interaction with animals or farm activities.']), 'is_active' => true],
            ['slug' => 'motel', 'name' => json_encode(['en' => 'Motel']), 'description' => json_encode(['en' => 'A roadside property designed for travelers, usually providing parking and easy access to rooms.']), 'is_active' => true],
            ['slug' => 'pension-inn', 'name' => json_encode(['en' => 'Pension / Inn']), 'description' => json_encode(['en' => 'A small hotel or guesthouse providing lodging and sometimes meals, typically family-run.']), 'is_active' => true],
            ['slug' => 'homestay', 'name' => json_encode(['en' => 'Homestay']), 'description' => json_encode(['en' => 'Accommodation within a local’s home, offering cultural immersion and personal interaction.']), 'is_active' => true],
            ['slug' => 'camping-tent', 'name' => json_encode(['en' => 'Camping / Tent']), 'description' => json_encode(['en' => 'Designated outdoor areas where guests can pitch tents or park campers, often with shared facilities.']), 'is_active' => true],
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
