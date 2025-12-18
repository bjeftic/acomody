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
        Schema::create('accommodation_draft_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('accommodation_draft_id')
                ->constrained('accommodation_drafts')
                ->onDelete('cascade');

            // Storage paths
            $table->string('disk')->default('minio'); // Storage disk (minio/s3)
            $table->string('path'); // Original file path in storage
            $table->string('thumbnail_path')->nullable(); // Thumbnail path
            $table->string('medium_path')->nullable(); // Medium size path
            $table->string('large_path')->nullable(); // Large size path

            // File information
            $table->string('original_filename');
            $table->string('mime_type');
            $table->unsignedInteger('file_size'); // Size in bytes
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();

            // Ordering and status
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');

            // Metadata
            $table->json('metadata')->nullable(); // EXIF, color palette, etc.
            $table->string('alt_text')->nullable();
            $table->string('caption')->nullable();

            // Tracking
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['accommodation_draft_id', 'order']);
            $table->index(['accommodation_draft_id', 'is_primary']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accommodation_draft_photos');
    }
};
