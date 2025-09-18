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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            $table->string('email_verification_token')->nullable()->after('email_verified_at');
            $table->timestamp('email_verification_token_expires_at')->nullable()->after('email_verification_token');
            // Account status and security
            $table->enum('status', ['active', 'inactive', 'suspended', 'deactivated'])->default('active')->after('email_verification_token_expires_at');
            $table->timestamp('last_login_at')->nullable()->after('status');
            $table->ipAddress('last_login_ip')->nullable()->after('last_login_at');
            $table->text('last_login_user_agent')->nullable()->after('last_login_ip');
            $table->ipAddress('registration_ip')->nullable()->after('last_login_user_agent');

            // Privacy and terms
            $table->timestamp('terms_accepted_at')->nullable()->after('registration_ip');
            $table->timestamp('privacy_policy_accepted_at')->nullable()->after('terms_accepted_at');
            $table->boolean('newsletter_subscription')->default(false)->after('privacy_policy_accepted_at');

            // Account management
            $table->timestamp('password_changed_at')->nullable()->after('newsletter_subscription');
            $table->timestamp('deactivated_at')->nullable()->after('password_changed_at');
            $table->string('deactivation_reason')->nullable()->after('deactivated_at');

            // Indexes for performance
            $table->index(['status', 'created_at']);
            $table->index('last_login_at');
            $table->index('email_verification_token');
            $table->integer('failed_login_attempts')->default(0)->after('email_verification_token');
            $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');

            $table->boolean('is_admin')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
