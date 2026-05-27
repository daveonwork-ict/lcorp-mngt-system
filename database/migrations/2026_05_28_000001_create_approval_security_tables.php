<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('rule_name');
            $table->string('module_name');
            $table->string('transaction_type')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->decimal('minimum_amount', 15, 2)->nullable();
            $table->decimal('maximum_amount', 15, 2)->nullable();
            $table->foreignId('approver_role_id')->constrained('roles')->cascadeOnDelete();
            $table->unsignedInteger('approval_level')->default(1);
            $table->boolean('requires_owner_approval')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['module_name', 'transaction_type', 'status']);
            $table->index(['branch_id', 'approval_level']);
        });

        Schema::create('approval_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('approval_number')->unique();
            $table->string('module_name');
            $table->string('reference_type');
            $table->unsignedBigInteger('reference_id');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('requested_at');
            $table->foreignId('current_approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('approval_level')->default(1);
            $table->string('priority')->default('normal');
            $table->string('status')->default('pending');
            $table->text('remarks')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('returned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('returned_at')->nullable();
            $table->text('return_reason')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();

            $table->index(['module_name', 'status']);
            $table->index(['branch_id', 'status']);
            $table->index(['reference_type', 'reference_id']);
        });

        Schema::create('approval_request_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('approval_request_id')->constrained('approval_requests')->cascadeOnDelete();
            $table->string('action');
            $table->text('remarks')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('performed_at');
            $table->timestamps();

            $table->index(['approval_request_id', 'performed_at']);
        });

        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('audit_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('module_name');
            $table->string('action_type');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->json('before_value')->nullable();
            $table->json('after_value')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_information')->nullable();
            $table->timestamps();

            $table->index(['module_name', 'action_type']);
            $table->index(['branch_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });

        Schema::create('login_attempt_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('login_identifier')->nullable();
            $table->string('status');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_information')->nullable();
            $table->timestamp('logged_at');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['status', 'logged_at']);
        });

        Schema::create('user_sessions', function (Blueprint $table): void {
            $table->id();
            $table->string('session_id')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_information')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('terminated_at')->nullable();
            $table->foreignId('terminated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('file_access_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('module_name');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_path')->nullable();
            $table->string('action_type')->default('download');
            $table->string('status')->default('success');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('accessed_at');
            $table->timestamps();

            $table->index(['module_name', 'accessed_at']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('backup_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('backup_number')->unique();
            $table->string('backup_type')->default('manual');
            $table->string('status')->default('pending');
            $table->string('file_path')->nullable();
            $table->decimal('file_size_mb', 12, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('started_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('security_alerts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('alert_type');
            $table->string('severity')->default('medium');
            $table->string('module_name')->nullable();
            $table->text('message');
            $table->json('context_payload')->nullable();
            $table->boolean('is_resolved')->default(false);
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('alerted_at');
            $table->timestamps();

            $table->index(['severity', 'is_resolved']);
            $table->index(['alert_type', 'alerted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_alerts');
        Schema::dropIfExists('backup_logs');
        Schema::dropIfExists('file_access_logs');
        Schema::dropIfExists('user_sessions');
        Schema::dropIfExists('login_attempt_logs');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('approval_request_logs');
        Schema::dropIfExists('approval_requests');
        Schema::dropIfExists('approval_rules');
    }
};
