<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->ulid('id')->primary();

            // Accommodation & parties
            $table->ulid('accommodation_id');
            $table->foreign('accommodation_id')->references('id')->on('accommodations')->cascadeOnDelete();

            $table->foreignId('user_id')->constrained('users');          // guest
            $table->foreignId('host_user_id')->constrained('users');     // host (denormalized)

            // Stay details
            $table->date('check_in');
            $table->date('check_out');
            $table->unsignedSmallInteger('nights');
            $table->unsignedSmallInteger('guests');

            // Status
            $table->enum('status', [
                'pending',
                'confirmed',
                'cancelled',
                'declined',
                'completed',
                'no_show',
            ])->default('pending')->index();

            // Booking type snapshot (instant_booking | request_to_book)
            $table->string('booking_type', 50)->default('instant_booking');

            // Pricing snapshot — stored at booking time to prevent price drift
            $table->string('currency', 10);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('fees_total', 10, 2)->default(0);
            $table->decimal('taxes_total', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2);
            $table->json('price_breakdown')->nullable();
            $table->json('optional_fee_ids')->nullable();

            // Payment
            $table->enum('payment_status', [
                'unpaid',
                'paid',
                'refunded',
                'partially_refunded',
            ])->default('unpaid');

            // Availability period reference — used to free dates on cancellation
            $table->ulid('availability_period_id')->nullable();

            // Confirmation
            $table->timestamp('confirmed_at')->nullable();

            // Cancellation
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('cancellation_reason')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();

            // Host decline
            $table->timestamp('declined_at')->nullable();
            $table->text('decline_reason')->nullable();

            // Guest notes
            $table->text('guest_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['accommodation_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['host_user_id', 'status']);
            $table->index(['check_in', 'check_out']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
