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
        Schema::create('priceable_items', function (Blueprint $table) {
            $table->ulid('id');

            $table->ulidMorphs('priceable'); // Creates priceable_id and priceable_type

            // Pricing Type - defines how this item is priced
            $table->enum('pricing_type', [
                'nightly',        // Per night (accommodations)
                'hourly',         // Per hour (services, meeting rooms)
                'daily',          // Per day (car rentals, equipment)
                'per_item',       // Per item (menu items, products)
                'per_person',     // Per person (tours, events)
                'per_table',      // Per table (restaurant reservations)
                'fixed',          // Fixed price (services)
                'custom'          // Custom pricing logic
            ])->index();

            // Base Pricing
            $table->decimal('base_price', 10, 2);
            $table->string('currency', 3)->default('EUR');

            // Base price in EUR for easier global comparisons and searches
            $table->decimal('base_price_eur', 10, 2)->nullable();

            // Weekend/Peak Pricing (optional)
            // $table->boolean('has_weekend_pricing')->default(false);
            // $table->decimal('weekend_price', 10, 2)->nullable();
            // $table->json('weekend_days')->nullable(); // ["friday", "saturday"]

            // Discounts (applicable to all types)
            // $table->decimal('bulk_discount_percent', 5, 2)->default(0); // Volume discount
            // $table->integer('bulk_discount_threshold')->nullable(); // e.g., 7+ nights, 10+ items

            // Minimum/Maximum Constraints
            $table->integer('min_quantity')->default(1); // min nights, min hours, min items
            $table->integer('max_quantity')->nullable(); // max nights, max hours, max items

            // Time-based constraints (flexible JSON storage)
            // $table->json('time_constraints')->nullable();
            /* Examples:

            For Accommodations:
            {
                "day_specific_min_stay": {
                    "friday": {"enabled": true, "quantity": 3},
                    "saturday": {"enabled": true, "quantity": 2}
                }
            }

            For Services:
            {
                "business_hours": {
                    "monday": {"start": "09:00", "end": "17:00"},
                    "friday": {"start": "09:00", "end": "15:00"}
                }
            }

            For Restaurants:
            {
                "meal_periods": {
                    "lunch": {"start": "12:00", "end": "15:00"},
                    "dinner": {"start": "18:00", "end": "22:00"}
                }
            }
            */

            // Active/Inactive
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['priceable_type', 'priceable_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('priceable_items');
    }
};
