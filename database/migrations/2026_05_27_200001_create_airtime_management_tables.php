<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('airtime_providers', function (Blueprint $table): void {
            $table->id();
            $table->string('provider_code')->unique();
            $table->string('provider_name');
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('default_commission_type')->default('none');
            $table->decimal('default_commission_value', 12, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('airtime_wallets', function (Blueprint $table): void {
            $table->id();
            $table->string('wallet_number')->unique();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('airtime_providers')->cascadeOnDelete();
            $table->decimal('beginning_balance', 12, 2)->default(0);
            $table->decimal('current_balance', 12, 2)->default(0);
            $table->decimal('low_balance_threshold', 12, 2)->default(1000);
            $table->string('status')->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['branch_id', 'provider_id']);
        });

        Schema::create('airtime_wallet_ledgers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('wallet_id')->constrained('airtime_wallets')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('airtime_providers')->cascadeOnDelete();
            $table->string('movement_type');
            $table->decimal('amount_in', 12, 2)->default(0);
            $table->decimal('amount_out', 12, 2)->default(0);
            $table->decimal('running_balance', 12, 2);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('airtime_wallet_fundings', function (Blueprint $table): void {
            $table->id();
            $table->string('funding_number')->unique();
            $table->foreignId('wallet_id')->constrained('airtime_wallets')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('airtime_providers')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('funding_date');
            $table->string('payment_method');
            $table->string('reference_number')->nullable();
            $table->string('proof_file')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('cashflow_prepared')->default(false);
            $table->json('cashflow_payload')->nullable();
            $table->timestamps();
        });

        Schema::create('airtime_transactions', function (Blueprint $table): void {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cashier_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('airtime_providers')->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained('airtime_wallets')->cascadeOnDelete();
            $table->string('customer_mobile_number');
            $table->decimal('load_amount', 12, 2);
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
            $table->string('payment_reference')->nullable();
            $table->string('transaction_status')->default('successful');
            $table->text('remarks')->nullable();
            $table->timestamp('processed_at');
            $table->timestamp('reversed_at')->nullable();
            $table->text('reversal_reason')->nullable();
            $table->boolean('cashflow_prepared')->default(false);
            $table->json('cashflow_payload')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'provider_id', 'customer_mobile_number']);
            $table->index(['transaction_status', 'processed_at']);
        });

        Schema::create('airtime_commissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('transaction_id')->constrained('airtime_transactions')->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('airtime_providers')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('commission_type');
            $table->decimal('commission_value', 12, 2)->default(0);
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->foreignId('computed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('airtime_wallet_adjustments', function (Blueprint $table): void {
            $table->id();
            $table->string('adjustment_number')->unique();
            $table->foreignId('wallet_id')->constrained('airtime_wallets')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('airtime_providers')->cascadeOnDelete();
            $table->string('adjustment_type');
            $table->decimal('amount', 12, 2);
            $table->string('reason');
            $table->string('status')->default('pending');
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_remarks')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('airtime_alerts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('provider_id')->nullable()->constrained('airtime_providers')->nullOnDelete();
            $table->foreignId('wallet_id')->nullable()->constrained('airtime_wallets')->nullOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained('airtime_transactions')->nullOnDelete();
            $table->string('alert_type');
            $table->string('severity')->default('medium');
            $table->text('message');
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('airtime_alerts');
        Schema::dropIfExists('airtime_wallet_adjustments');
        Schema::dropIfExists('airtime_commissions');
        Schema::dropIfExists('airtime_transactions');
        Schema::dropIfExists('airtime_wallet_fundings');
        Schema::dropIfExists('airtime_wallet_ledgers');
        Schema::dropIfExists('airtime_wallets');
        Schema::dropIfExists('airtime_providers');
    }
};
