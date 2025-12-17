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
            $table->foreignId('accommodation_type_id')->constrained()->cascadeOnDelete();
            $table->enum('accommodation_occupation', ['entire-place', 'private-room', 'shared-room']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('booking_type', ['instant-booking', 'request-to-book']);
            $table->json('amenities')->nullable();
            $table->json('house_rules')->nullable();
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
