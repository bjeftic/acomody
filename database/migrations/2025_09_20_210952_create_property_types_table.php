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
        Schema::create('property_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        \DB::table('property_types')->insert([
            ['name' => 'Apartment', 'description' => 'A private, self-contained unit within a building, featuring its own kitchen, bathroom, and living area—ideal for independent stays.'],
            ['name' => 'House', 'description' => 'A detached residential property offering full privacy and exclusive use of all rooms and outdoor areas.'],
            ['name' => 'Villa', 'description' => 'A spacious and upscale property, often with a private garden, terrace, or pool—designed for luxury stays.'],
            ['name' => 'Studio', 'description' => 'A single-room unit combining living, sleeping, and kitchen areas, suitable for short stays or solo travelers.'],
            ['name' => 'Hotel', 'description' => 'A professionally managed property offering private rooms, services, and amenities such as reception, housekeeping, and often breakfast.'],
            ['name' => 'Townhouse', 'description' => 'A multi-story residence sharing walls with neighboring units, combining private living with a community feel.'],
            ['name' => 'Condominium', 'description' => 'A unit within a residential complex where facilities such as pools, gyms, or shared spaces are often available.'],
            ['name' => 'Bungalow', 'description' => 'A single-story house, often with a veranda or garden, providing easy access and comfort.'],
            ['name' => 'Guesthouse', 'description' => 'A small residential property offering private rooms and sometimes meals, with a homely atmosphere.'],
            ['name' => 'Resort', 'description' => 'A large property offering extensive amenities like pools, restaurants, sports facilities, and entertainment for leisure stays.'],
            ['name' => 'Hostel', 'description' => 'Budget-friendly accommodation with shared rooms or dormitories, ideal for travelers seeking social experiences.'],
            ['name' => 'Cabin', 'description' => 'A rustic, often wooden property, typically located in nature, ideal for outdoor enthusiasts and retreats.'],
            ['name' => 'Cottage', 'description' => 'A small, cozy house, usually in a rural or semi-rural location, perfect for relaxed getaways.'],
            ['name' => 'Farm stay', 'description' => 'A rural accommodation experience on a working farm, often including interaction with animals or farm activities.'],
            ['name' => 'Motel', 'description' => 'A roadside property designed for travelers, usually providing parking and easy access to rooms.'],
            ['name' => 'Pension / Inn', 'description' => 'A small hotel or guesthouse providing lodging and sometimes meals, typically family-run.'],
            ['name' => 'Homestay', 'description' => 'Accommodation within a local’s home, offering cultural immersion and personal interaction.'],
            ['name' => 'Camping / Tent', 'description' => 'Designated outdoor areas where guests can pitch tents or park campers, often with shared facilities.'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_types');
    }
};
