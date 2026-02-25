<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fees', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulidMorphs('feeable');
            $table->string('fee_type');
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->string('charge_type')->default('per_booking');
            $table->decimal('percentage_rate', 5, 2)->nullable();
            $table->string('percentage_basis')->nullable();
            $table->integer('applies_after_quantity')->nullable();
            $table->integer('applies_after_persons')->nullable();
            $table->decimal('applies_after_amount', 10, 2)->nullable();
            $table->boolean('mandatory')->default(true);
            $table->boolean('is_refundable')->default(false);
            $table->integer('refund_days')->nullable();
            $table->decimal('refund_percentage', 5, 2)->nullable();
            $table->boolean('is_taxable')->default(true);
            $table->boolean('show_in_breakdown')->default(true);
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['feeable_type', 'feeable_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};
