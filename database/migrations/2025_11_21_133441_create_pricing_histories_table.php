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
        Schema::create('pricing_histories', function (Blueprint $table) {
            $table->ulid('id')->primary();

            // Polymorphic - tracks changes for any entity
            $table->ulidMorphs('priceable');

            // What changed
            $table->enum('change_type', [
                'base_price',         // Base pricing change
                'period_pricing',     // Seasonal/special period pricing
                'fee',                // Fee added/modified/removed
                'tax',                // Tax assignment/modification
                'discount',           // Discount rule change
                'availability',       // Availability/blocking change
                'bulk_update'         // Bulk update of multiple fields
            ])->index();

            // Detailed change info
            $table->string('field_name')->nullable(); // Specific field changed (e.g., 'base_price', 'cleaning_fee')
            $table->json('old_values')->nullable(); // Previous values
            $table->json('new_values'); // New values

            // Change metadata
            $table->text('change_reason')->nullable(); // Why was this changed?
            $table->enum('change_source', [
                'manual',             // Manual change by user
                'api',                // API update
                'bulk_import',        // Bulk import/sync
                'automation',         // Automated rule
                'external_sync',      // External platform sync
                'system'              // System-generated
            ])->default('manual');

            // Who changed it
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users');
            $table->string('changed_by_system')->nullable(); // "dynamic_pricing_engine", "airbnb_sync"
            $table->ipAddress('changed_from_ip')->nullable();

            // When it was changed
            $table->timestamp('changed_at')->index();

            // Rollback support
            $table->boolean('can_rollback')->default(false);
            $table->timestamp('rolled_back_at')->nullable();
            $table->foreignId('rolled_back_by_user_id')->nullable()->constrained('users');

            $table->timestamps();

            // Indexes
            $table->index(['priceable_type', 'priceable_id', 'changed_at']);
            $table->index(['change_type', 'changed_at']);
            $table->index('changed_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_histories');
    }
};
