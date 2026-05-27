<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_import_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('import_number')->unique();
            $table->string('module_name');
            $table->string('file_name');
            $table->string('file_path')->nullable();
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('successful_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->string('status')->default('previewed');
            $table->json('summary_payload')->nullable();
            $table->string('rejected_rows_path')->nullable();
            $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('imported_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['module_name', 'status']);
            $table->index(['imported_by', 'created_at']);
        });

        Schema::create('data_import_errors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('data_import_log_id')->constrained('data_import_logs')->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->json('row_payload')->nullable();
            $table->json('error_messages')->nullable();
            $table->timestamps();

            $table->index(['data_import_log_id', 'row_number']);
        });

        Schema::create('training_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('training_number')->unique();
            $table->string('training_group');
            $table->string('session_title');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('facilitator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('attendee_name');
            $table->string('attendee_role');
            $table->string('status')->default('completed');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['training_group', 'status']);
            $table->index(['branch_id', 'scheduled_at']);
        });

        Schema::create('deployment_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('deployment_number')->unique();
            $table->string('item_key');
            $table->string('item_label');
            $table->string('status')->default('pending');
            $table->text('remarks')->nullable();
            $table->json('meta_payload')->nullable();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            $table->index(['item_key', 'status']);
        });

        Schema::create('go_live_checklists', function (Blueprint $table): void {
            $table->id();
            $table->string('checklist_number')->unique();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('item_key');
            $table->string('item_label');
            $table->string('status')->default('pending');
            $table->text('remarks')->nullable();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status']);
            $table->index(['item_key', 'status']);
        });

        Schema::create('support_tickets', function (Blueprint $table): void {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('module_name');
            $table->text('issue_description');
            $table->string('priority')->default('medium');
            $table->string('status')->default('open');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'priority']);
            $table->index(['branch_id', 'status']);
        });

        Schema::create('system_acceptance_records', function (Blueprint $table): void {
            $table->id();
            $table->string('acceptance_number')->unique();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('accepted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('acceptance_date')->nullable();
            $table->json('criteria_payload')->nullable();
            $table->string('status')->default('draft');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_acceptance_records');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('go_live_checklists');
        Schema::dropIfExists('deployment_logs');
        Schema::dropIfExists('training_logs');
        Schema::dropIfExists('data_import_errors');
        Schema::dropIfExists('data_import_logs');
    }
};
