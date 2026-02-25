<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entity_taxes', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulidMorphs('taxable');
            $table->foreignId('tax_rate_id')->constrained('tax_rates')->cascadeOnDelete();
            $table->boolean('use_override')->default(false);
            $table->decimal('override_rate_percent', 5, 2)->nullable();
            $table->decimal('override_flat_amount', 10, 2)->nullable();
            $table->boolean('override_included_in_price')->nullable();
            $table->string('override_calculation_basis')->nullable();
            $table->boolean('is_exempt')->default(false);
            $table->string('exemption_reason')->nullable();
            $table->string('exemption_certificate')->nullable();
            $table->date('exemption_valid_until')->nullable();
            $table->jsonb('custom_rules')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['taxable_type', 'taxable_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entity_taxes');
    }
};
