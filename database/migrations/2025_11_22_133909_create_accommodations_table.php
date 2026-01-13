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
