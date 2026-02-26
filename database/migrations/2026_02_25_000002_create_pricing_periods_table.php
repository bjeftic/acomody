<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_periods', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulidMorphs('priceable');
            $table->string('period_type');
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->jsonb('applicable_days')->nullable();
            $table->decimal('price_override', 10, 2)->nullable();
            $table->decimal('price_multiplier', 5, 2)->nullable();
            $table->decimal('price_adjustment', 10, 2)->nullable();
            $table->integer('min_quantity_override')->nullable();
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_periods');
    }
};
