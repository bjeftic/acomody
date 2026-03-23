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
        Schema::create('legal_document_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legal_document_id')->constrained()->cascadeOnDelete();
            $table->string('section_type'); // 'heading' | 'subheading' | 'paragraph'
            $table->json('content');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['legal_document_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_document_sections');
    }
};
