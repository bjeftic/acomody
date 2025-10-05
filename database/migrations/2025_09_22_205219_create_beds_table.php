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
        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seeding bed types
        \DB::table('beds')->insert([
            ['name' => 'Double Bed', 'description' => '131-150 cm (52-59 inches) wide', 'is_active' => true],
            ['name' => 'King Bed', 'description' => '181-210 cm (71-82 inches) wide', 'is_active' => true],
            ['name' => 'Queen Bed', 'description' => '151-180 cm (60-70 inches) wide', 'is_active' => true],
            ['name' => 'Single Bed', 'description' => '90-130 cm (35-51 inches) wide', 'is_active' => true],
            ['name' => 'Twin Bed', 'description' => '90-130 cm (35-51 inches) wide', 'is_active' => true],
            ['name' => 'Bunk Bed', 'description' => null, 'is_active' => true],
            ['name' => 'Sofa Bed', 'description' => null, 'is_active' => true],
            ['name' => 'Futon Mat', 'description' => null, 'is_active' => true],
        ]);
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beds');
    }
};
