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
        Schema::table('accommodation_drafts', function (Blueprint $table) {
            $table->unsignedBigInteger('locked_by_id')->nullable()->after('last_saved_at');
            $table->timestamp('locked_at')->nullable()->after('locked_by_id');

            $table->foreign('locked_by_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accommodation_drafts', function (Blueprint $table) {
            $table->dropForeign(['locked_by_id']);
            $table->dropColumn(['locked_by_id', 'locked_at']);
        });
    }
};
