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
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('from_currency', 3); // Base currency (usually EUR)
            $table->string('to_currency', 3); // Target currency
            $table->decimal('rate', 16, 6); // Exchange rate
            $table->date('date'); // Date of the rate
            $table->string('source', 50)->nullable(); // API source (exchangerate-api, fixer, etc.)
            $table->boolean('is_active')->default(true); // Is this rate currently active
            $table->timestamps();

            // Indexes for performance
            $table->index(['to_currency', 'date', 'is_active']);
            $table->index(['from_currency', 'to_currency', 'date']);
            $table->index('date');

            // Unique constraint: one rate per currency pair per day
            $table->unique(['from_currency', 'to_currency', 'date', 'source']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
