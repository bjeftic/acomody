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
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();

            // Geographic Scope
            $table->string('country_code', 2)->index(); // ISO 3166-1 alpha-2 (e.g., 'RS', 'DE', 'FR')
            $table->string('region_code', 10)->nullable()->index(); // State/Province (e.g., 'CA', 'NY', 'Vojvodina')
            $table->string('city', 100)->nullable()->index(); // City-specific tax

            // Tax Details
            $table->string('tax_name'); // e.g., "VAT", "Tourist Tax", "Service Tax"
            $table->enum('tax_type', [
                'vat',                // Value Added Tax
                'sales',              // Sales Tax
                'tourist',            // Tourism/Occupancy Tax
                'city',               // City Tax
                'service',            // Service Tax (for restaurants)
                'environmental',      // Environmental/Sustainability Tax
                'luxury',             // Luxury Tax
                'other'
            ])->index();

            // Applicable to (if empty, applies to all)
            $table->json('applicable_types')->nullable();
            /* Example:
            ["accommodation", "restaurant"]  // Only applies to these types
            null                             // Applies to all types
            */

            // Rate Configuration
            $table->decimal('rate_percent', 5, 2)->nullable(); // Percentage rate (e.g., 20.00 for 20%)
            $table->decimal('flat_amount', 10, 2)->nullable(); // Flat amount per unit/person

            $table->enum('calculation_basis', [
                'subtotal_only',          // Tax only on base price
                'subtotal_and_fees',      // Tax on base price + fees
                'per_unit',               // Flat rate per night/hour/item
                'per_person_per_unit'     // Flat rate per person per night/hour
            ])->default('subtotal_and_fees');

            // Application Rules
            $table->boolean('included_in_price')->default(false);
            $table->integer('max_units')->nullable(); // Some tourist taxes cap at X nights
            $table->integer('min_age')->nullable(); // Some taxes exempt children
            $table->integer('max_age')->nullable(); // Some taxes exempt seniors

            // Exemptions
            $table->decimal('exempt_below_amount', 10, 2)->nullable(); // Exempt if total below X
            $table->json('exempt_categories')->nullable(); // Exempt categories
            /* Example:
            ["budget", "hostel", "camping"]
            */

            // Effective Date Range
            $table->date('effective_from');
            $table->date('effective_until')->nullable();

            // Priority (for multiple taxes in same region)
            $table->integer('priority')->default(0);

            // Metadata
            $table->string('legislation_reference')->nullable(); // Legal reference
            $table->text('notes')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['country_code', 'region_code', 'is_active']);
            $table->index(['effective_from', 'effective_until']);
            $table->index(['tax_type', 'is_active']);
        });

        $taxRates = [
            // ============================================
            // SERBIA (RS)
            // ============================================
            [
                'country_code' => 'RS',
                'region_code' => null,
                'city' => null,
                'tax_name' => 'VAT (PDV)',
                'tax_type' => 'vat',
                'applicable_types' => json_encode(['accommodation', 'restaurant', 'service']),
                'rate_percent' => 20.00,
                'flat_amount' => null,
                'calculation_basis' => 'subtotal_and_fees',
                'included_in_price' => true,
                'max_units' => null,
                'min_age' => null,
                'max_age' => null,
                'exempt_below_amount' => null,
                'exempt_categories' => null,
                'effective_from' => '2024-01-01',
                'effective_until' => null,
                'priority' => 1,
                'legislation_reference' => 'Zakon o PDV-u Republike Srbije',
                'notes' => 'Standard VAT rate for all services in Serbia',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'country_code' => 'RS',
                'region_code' => null,
                'city' => null,
                'tax_name' => 'Tourist Tax (Boravišna taksa)',
                'tax_type' => 'tourist',
                'applicable_types' => json_encode(['accommodation']),
                'rate_percent' => null,
                'flat_amount' => 1.50,
                'calculation_basis' => 'per_person_per_unit',
                'included_in_price' => false,
                'max_units' => 30,
                'min_age' => 18,
                'max_age' => null,
                'exempt_below_amount' => null,
                'exempt_categories' => null,
                'effective_from' => '2024-01-01',
                'effective_until' => null,
                'priority' => 2,
                'legislation_reference' => 'Zakon o boravišnoj taksi',
                'notes' => 'Tourist tax applies to adults (18+), max 30 nights',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ============================================
            // GERMANY (DE)
            // ============================================
            [
                'country_code' => 'DE',
                'region_code' => null,
                'city' => null,
                'tax_name' => 'VAT (Mehrwertsteuer)',
                'tax_type' => 'vat',
                'applicable_types' => json_encode(['accommodation', 'restaurant', 'service']),
                'rate_percent' => 19.00,
                'flat_amount' => null,
                'calculation_basis' => 'subtotal_and_fees',
                'included_in_price' => true,
                'max_units' => null,
                'min_age' => null,
                'max_age' => null,
                'exempt_below_amount' => null,
                'exempt_categories' => null,
                'effective_from' => '2024-01-01',
                'effective_until' => null,
                'priority' => 1,
                'legislation_reference' => 'Umsatzsteuergesetz (UStG)',
                'notes' => 'Standard VAT rate in Germany',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'country_code' => 'DE',
                'region_code' => null,
                'city' => null,
                'tax_name' => 'Reduced VAT (Ermäßigter Steuersatz)',
                'tax_type' => 'vat',
                'applicable_types' => json_encode(['restaurant']), // Food only
                'rate_percent' => 7.00,
                'flat_amount' => null,
                'calculation_basis' => 'subtotal_only',
                'included_in_price' => true,
                'max_units' => null,
                'min_age' => null,
                'max_age' => null,
                'exempt_below_amount' => null,
                'exempt_categories' => json_encode(['food']),
                'effective_from' => '2024-01-01',
                'effective_until' => null,
                'priority' => 1,
                'legislation_reference' => 'UStG § 12 Abs. 2',
                'notes' => 'Reduced VAT for food items',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'country_code' => 'DE',
                'region_code' => null,
                'city' => 'Berlin',
                'tax_name' => 'City Tax (Bettensteuer)',
                'tax_type' => 'city',
                'applicable_types' => json_encode(['accommodation']),
                'rate_percent' => 5.00,
                'flat_amount' => null,
                'calculation_basis' => 'subtotal_only',
                'included_in_price' => false,
                'max_units' => null,
                'min_age' => null,
                'max_age' => null,
                'exempt_below_amount' => null,
                'exempt_categories' => null,
                'effective_from' => '2024-01-01',
                'effective_until' => null,
                'priority' => 2,
                'legislation_reference' => 'Übernachtungsteuergesetz Berlin',
                'notes' => 'Berlin city tax on private accommodation',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ============================================
            // FRANCE (FR)
            // ============================================
            [
                'country_code' => 'FR',
                'region_code' => null,
                'city' => null,
                'tax_name' => 'VAT (TVA)',
                'tax_type' => 'vat',
                'applicable_types' => json_encode(['accommodation', 'restaurant', 'service']),
                'rate_percent' => 20.00,
                'flat_amount' => null,
                'calculation_basis' => 'subtotal_and_fees',
                'included_in_price' => true,
                'max_units' => null,
                'min_age' => null,
                'max_age' => null,
                'exempt_below_amount' => null,
                'exempt_categories' => null,
                'effective_from' => '2024-01-01',
                'effective_until' => null,
                'priority' => 1,
                'legislation_reference' => 'Code général des impôts',
                'notes' => 'Standard VAT rate in France',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'country_code' => 'FR',
                'region_code' => null,
                'city' => 'Paris',
                'tax_name' => 'Tourist Tax (Taxe de séjour)',
                'tax_type' => 'tourist',
                'applicable_types' => json_encode(['accommodation']),
                'rate_percent' => null,
                'flat_amount' => 2.50,
                'calculation_basis' => 'per_person_per_unit',
                'included_in_price' => false,
                'max_units' => null,
                'min_age' => 18,
                'max_age' => null,
                'exempt_below_amount' => null,
                'exempt_categories' => null,
                'effective_from' => '2024-01-01',
                'effective_until' => null,
                'priority' => 2,
                'legislation_reference' => 'Code général des collectivités territoriales',
                'notes' => 'Paris tourist tax, amount varies by accommodation type',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ============================================
            // USA - NEW YORK (US-NY)
            // ============================================
            [
                'country_code' => 'US',
                'region_code' => 'NY',
                'city' => 'New York City',
                'tax_name' => 'Sales Tax',
                'tax_type' => 'sales',
                'applicable_types' => json_encode(['accommodation', 'restaurant', 'service']),
                'rate_percent' => 8.875,
                'flat_amount' => null,
                'calculation_basis' => 'subtotal_and_fees',
                'included_in_price' => false,
                'max_units' => null,
                'min_age' => null,
                'max_age' => null,
                'exempt_below_amount' => null,
                'exempt_categories' => null,
                'effective_from' => '2024-01-01',
                'effective_until' => null,
                'priority' => 1,
                'legislation_reference' => 'New York State Tax Law',
                'notes' => 'Combined state and local sales tax for NYC',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'country_code' => 'US',
                'region_code' => 'NY',
                'city' => 'New York City',
                'tax_name' => 'Hotel Occupancy Tax',
                'tax_type' => 'tourist',
                'applicable_types' => json_encode(['accommodation']),
                'rate_percent' => 5.875,
                'flat_amount' => null,
                'calculation_basis' => 'subtotal_only',
                'included_in_price' => false,
                'max_units' => null,
                'min_age' => null,
                'max_age' => null,
                'exempt_below_amount' => null,
                'exempt_categories' => null,
                'effective_from' => '2024-01-01',
                'effective_until' => null,
                'priority' => 2,
                'legislation_reference' => 'NYC Administrative Code',
                'notes' => 'NYC hotel room occupancy tax',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ============================================
            // UNITED KINGDOM (GB)
            // ============================================
            [
                'country_code' => 'GB',
                'region_code' => null,
                'city' => null,
                'tax_name' => 'VAT',
                'tax_type' => 'vat',
                'applicable_types' => json_encode(['accommodation', 'restaurant', 'service']),
                'rate_percent' => 20.00,
                'flat_amount' => null,
                'calculation_basis' => 'subtotal_and_fees',
                'included_in_price' => true,
                'max_units' => null,
                'min_age' => null,
                'max_age' => null,
                'exempt_below_amount' => null,
                'exempt_categories' => null,
                'effective_from' => '2024-01-01',
                'effective_until' => null,
                'priority' => 1,
                'legislation_reference' => 'Value Added Tax Act 1994',
                'notes' => 'Standard VAT rate in UK',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        \DB::table('tax_rates')->insert($taxRates);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};
