<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pwa_install_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('platform')->nullable();
            $table->string('browser')->nullable();
            $table->string('device_type')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('installed_at');
            $table->timestamps();

            $table->index(['installed_at', 'platform']);
        });

        Schema::create('push_subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('endpoint')->unique();
            $table->text('public_key')->nullable();
            $table->text('auth_token')->nullable();
            $table->json('subscription_payload')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });

        Schema::create('device_tokens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('token')->unique();
            $table->string('platform')->nullable();
            $table->string('device_name')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('user_device_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->json('preferences')->nullable();
            $table->boolean('allow_low_stock')->default(true);
            $table->boolean('allow_cash_variance')->default(true);
            $table->boolean('allow_pending_approval')->default(true);
            $table->boolean('allow_announcement')->default(true);
            $table->boolean('allow_chat_message')->default(true);
            $table->boolean('allow_low_wallet')->default(true);
            $table->boolean('allow_warranty_update')->default(true);
            $table->boolean('allow_daily_closing')->default(true);
            $table->timestamps();

            $table->unique(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_device_preferences');
        Schema::dropIfExists('device_tokens');
        Schema::dropIfExists('push_subscriptions');
        Schema::dropIfExists('pwa_install_logs');
    }
};
