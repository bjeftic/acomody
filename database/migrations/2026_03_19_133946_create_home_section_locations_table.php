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
        Schema::create('home_section_locations', function (Blueprint $table) {
            $table->id();
            $table->char('home_section_id', 26);
            $table->foreign('home_section_id')->references('id')->on('home_sections')->onDelete('cascade');
            $table->char('location_id', 26);
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_section_locations');
    }
};
