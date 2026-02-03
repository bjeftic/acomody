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
        Schema::create('accommodations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('accommodation_draft_id');
            $table->enum('accommodation_type', [
                'apartment',
                'house',
                'villa',
                'condo',
                'hotel',
                'cottage',
                'cabin',
                'studio',
                'bed_breakfast',
                'townhouse',
                'chalet',
                'bungalow',
                'guesthouse',
                'loft',
                'penthouse',
                'resort',
                'guest_suite',
                'hostel',
                'farm_stay',
                'barn',
                'treehouse',
                'houseboat',
                'boat',
                'camper_rv',
                'tiny_house',
                'glamping',
                'castle',
                'yacht',
                'dome',
                'tent',
                'yurt',
                'container',
                'cave',
                'lighthouse',
                'windmill',
                'earth_house',
                'cycladic_home',
                'trullo',
                'riad',
                'ryokan',
                'shepherd_house',
                'igloo'
            ]);
            $table->enum('accommodation_occupation', ['entire_place', 'private_room', 'shared_room']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('booking_type', ['instant_booking', 'request_to_book']);
            $table->integer('max_guests')->default(1);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('favorites_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->string('street_address')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->time('check_in_from')->nullable();
            $table->time('check_in_until')->nullable();
            $table->time('check_out_until')->nullable();
            $table->time('quiet_hours_from')->nullable();
            $table->time('quiet_hours_until')->nullable();
            $table->enum('cancellation_policy', ['flexible', 'moderate', 'firm', 'strict', 'non-refundable'])->default('moderate');
            $table->integer('bedrooms')->default(1);
            $table->integer('beds')->default(1);
            $table->integer('bathrooms')->default(1);
            $table->boolean('is_active')->default(false);
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accommodations');
    }
};
