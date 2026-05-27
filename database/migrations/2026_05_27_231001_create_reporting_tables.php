<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_exports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete()->cascadeOnUpdate();
            $table->string('report_type');
            $table->string('export_format');
            $table->string('file_name')->nullable();
            $table->json('filters_used')->nullable();
            $table->string('status')->default('generated');
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->index(['report_type', 'export_format']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('dashboard_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('dashboard_key');
            $table->json('preferences')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'dashboard_key']);
        });

        Schema::create('analytics_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->string('snapshot_key');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete()->cascadeOnUpdate();
            $table->date('snapshot_date');
            $table->json('metrics');
            $table->timestamps();

            $table->index(['snapshot_key', 'snapshot_date']);
            $table->index(['branch_id', 'snapshot_date']);
        });

        Schema::create('scheduled_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete()->cascadeOnUpdate();
            $table->string('report_type');
            $table->string('schedule_frequency');
            $table->json('filters')->nullable();
            $table->string('delivery_channel')->default('in_app');
            $table->string('status')->default('active');
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index(['status', 'next_run_at']);
        });

        Schema::create('report_filters', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('filter_name');
            $table->string('report_type');
            $table->json('filter_payload');
            $table->timestamps();

            $table->index(['user_id', 'report_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_filters');
        Schema::dropIfExists('scheduled_reports');
        Schema::dropIfExists('analytics_snapshots');
        Schema::dropIfExists('dashboard_preferences');
        Schema::dropIfExists('report_exports');
    }
};
