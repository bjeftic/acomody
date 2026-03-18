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
        Schema::create('ical_calendars', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('accommodation_id')->constrained()->cascadeOnDelete();
            $table->string('source'); // airbnb, booking, other
            $table->string('name')->nullable();
            $table->text('ical_url');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index(['accommodation_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ical_calendars');
    }
};
