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
        Schema::create('accommodation_drafts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('current_step')->default(1);
            $table->enum('status', ['draft', 'waiting_for_approval', 'processing', 'published'])->default('draft');
            $table->json('data'); // Store draft data as JSON
            $table->timestamp('last_saved_at')->nullable();
            $table->timestamps();
        });

        // Add a unique index to ensure one draft per user with 'draft' status
        \DB::statement('
            CREATE UNIQUE INDEX unique_user_draft_status
            ON accommodation_drafts (user_id)
            WHERE status = \'draft\';
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accommodation_drafts');
    }
};
