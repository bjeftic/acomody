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
        Schema::create('availability_periods', function (Blueprint $table) {
            $table->ulid('id');

            // Polymorphic - works for everything
            $table->ulidMorphs('available'); // available_id, available_type

            // Date Range
            $table->date('start_date');
            $table->date('end_date');

            // Time Range (optional - for services with specific hours)
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            // Days of week (for recurring availability/blocks)
            $table->json('recurring_days')->nullable(); // ["monday", "tuesday"]
            /* Example for restaurant closed on Mondays:
            {
                "days": ["monday"],
                "repeat": "weekly"
            }
            */

            // Status
            $table->enum('status', [
                'available',      // Explicitly available (overrides blocks)
                'blocked',        // Blocked by owner
                'booked',         // Already booked/reserved
                'maintenance',    // Under maintenance
                'closed',         // Closed (restaurant closed day, service unavailable)
                'sold_out'        // Sold out (events, limited capacity)
            ])->default('blocked')->index();

            // Reason for blocking
            $table->enum('reason', [
                'owner_blocked',      // Owner manually blocked
                'maintenance',        // Maintenance/renovation
                'booking',            // Already booked
                'external_booking',   // Booked on another platform
                'holiday',            // Holiday/vacation
                'closed_day',         // Regular closed day (e.g., Monday)
                'capacity_reached',   // Capacity limit reached
                'weather',            // Weather-related closure
                'event',              // Private event
                'other'
            ])->nullable();

            // Additional info
            $table->text('notes')->nullable();

            // Capacity management (for restaurants, events)
            $table->integer('max_capacity')->nullable(); // Total capacity
            $table->integer('current_bookings')->default(0); // Current bookings count

            $table->timestamps();

            // Indexes
            $table->index(['available_type', 'available_id', 'status']);
            $table->index(['start_date', 'end_date']);
            $table->index(['available_type', 'available_id', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availability_periods');
    }
};
