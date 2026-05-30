<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cash_outs')) {
            Schema::create('cash_outs', function (Blueprint $table): void {
                $table->id();
                $table->string('cash_out_number')->unique();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->string('source_type');
                $table->string('source_reference_type')->nullable();
                $table->unsignedBigInteger('source_reference_id')->nullable();
                $table->decimal('amount', 12, 2);
                $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
                $table->foreignId('released_by')->constrained('users')->cascadeOnDelete();
                $table->timestamp('released_at');
                $table->text('remarks')->nullable();
                $table->string('status')->default('posted');
                $table->timestamps();

                $table->index(['branch_id', 'released_at']);
                $table->index(['source_type', 'source_reference_type', 'source_reference_id'], 'cash_outs_src_ref_idx');
            });
        }

        if (! Schema::hasTable('expense_categories')) {
            Schema::create('expense_categories', function (Blueprint $table): void {
                $table->id();
                $table->string('category_code')->unique();
                $table->string('category_name');
                $table->text('description')->nullable();
                $table->boolean('requires_approval')->default(true);
                $table->boolean('receipt_required')->default(false);
                $table->decimal('monthly_budget_limit', 12, 2)->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('expenses')) {
            Schema::create('expenses', function (Blueprint $table): void {
                $table->id();
                $table->string('expense_number')->unique();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->foreignId('category_id')->constrained('expense_categories')->cascadeOnDelete();
                $table->date('expense_date');
                $table->string('vendor_or_payee');
                $table->decimal('amount', 12, 2);
                $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
                $table->text('description')->nullable();
                $table->string('status')->default('draft');
                $table->foreignId('submitted_by')->constrained('users')->cascadeOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('rejected_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['branch_id', 'expense_date']);
                $table->index(['status', 'expense_date']);
            });
        }

        if (! Schema::hasTable('expense_attachments')) {
            Schema::create('expense_attachments', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('expense_id')->constrained('expenses')->cascadeOnDelete();
                $table->string('file_name');
                $table->string('file_path');
                $table->string('file_type');
                $table->unsignedBigInteger('file_size');
                $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('daily_closings')) {
            Schema::create('daily_closings', function (Blueprint $table): void {
                $table->id();
                $table->string('closing_number')->unique();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->foreignId('cashier_id')->constrained('users')->cascadeOnDelete();
                $table->date('closing_date');
                $table->decimal('opening_cash', 12, 2)->default(0);
                $table->decimal('product_sales_cash', 12, 2)->default(0);
                $table->decimal('airtime_sales_cash', 12, 2)->default(0);
                $table->decimal('other_cash_in', 12, 2)->default(0);
                $table->decimal('total_cash_in', 12, 2)->default(0);
                $table->decimal('total_cash_out', 12, 2)->default(0);
                $table->decimal('expected_cash', 12, 2)->default(0);
                $table->decimal('actual_cash', 12, 2)->default(0);
                $table->decimal('variance_amount', 12, 2)->default(0);
                $table->string('variance_type')->default('balanced');
                $table->text('variance_explanation')->nullable();
                $table->text('remarks')->nullable();
                $table->string('status')->default('draft');
                $table->foreignId('submitted_by')->constrained('users')->cascadeOnDelete();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();

                $table->index(['branch_id', 'closing_date']);
                $table->index(['status', 'closing_date']);
            });
        }

        if (! Schema::hasTable('cash_denominations')) {
            Schema::create('cash_denominations', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('daily_closing_id')->constrained('daily_closings')->cascadeOnDelete();
                $table->decimal('denomination', 12, 2);
                $table->unsignedInteger('quantity')->default(0);
                $table->decimal('total_amount', 12, 2)->default(0);
                $table->timestamps();

                $table->unique(['daily_closing_id', 'denomination']);
            });
        }

        if (! Schema::hasTable('cash_variances')) {
            Schema::create('cash_variances', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('daily_closing_id')->constrained('daily_closings')->cascadeOnDelete();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->foreignId('cashier_id')->constrained('users')->cascadeOnDelete();
                $table->decimal('expected_cash', 12, 2);
                $table->decimal('actual_cash', 12, 2);
                $table->decimal('variance_amount', 12, 2);
                $table->string('variance_type');
                $table->text('explanation')->nullable();
                $table->string('resolution_status')->default('pending');
                $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();

                $table->index(['branch_id', 'resolution_status']);
            });
        }

        if (! Schema::hasTable('fund_transfers')) {
            Schema::create('fund_transfers', function (Blueprint $table): void {
                $table->id();
                $table->string('transfer_number')->unique();
                $table->foreignId('source_branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->foreignId('destination_branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->decimal('amount', 12, 2);
                $table->string('transfer_method');
                $table->string('reference_number')->nullable();
                $table->string('proof_file')->nullable();
                $table->string('status')->default('pending');
                $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->index(['status', 'created_at']);
                $table->index(['source_branch_id', 'destination_branch_id']);
            });
        }

        if (! Schema::hasTable('financial_ledgers')) {
            Schema::create('financial_ledgers', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->string('ledger_type');
                $table->string('reference_type');
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->decimal('amount_in', 12, 2)->default(0);
                $table->decimal('amount_out', 12, 2)->default(0);
                $table->decimal('running_balance', 12, 2)->nullable();
                $table->text('description')->nullable();
                $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['branch_id', 'created_at']);
                $table->index(['ledger_type', 'reference_type']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_ledgers');
        Schema::dropIfExists('fund_transfers');
        Schema::dropIfExists('cash_variances');
        Schema::dropIfExists('cash_denominations');
        Schema::dropIfExists('daily_closings');
        Schema::dropIfExists('expense_attachments');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('cash_outs');
    }
};
