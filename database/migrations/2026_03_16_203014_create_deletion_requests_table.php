<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deletion_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // host_account, accommodation, etc.
            $table->unsignedBigInteger('subject_id')->nullable(); // ID of the subject (e.g. accommodation_id), null for host_account
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('reason')->nullable(); // admin rejection reason or notes
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deletion_requests');
    }
};
