<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table): void {
            $table->id();
            $table->string('supplier_code')->unique();
            $table->string('supplier_name');
            $table->string('contact_person')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->json('product_categories')->nullable();
            $table->string('payment_terms')->nullable();
            $table->string('status')->default('active');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['status', 'supplier_name']);
        });

        Schema::create('purchase_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->date('request_date');
            $table->string('purpose');
            $table->string('priority')->default('normal');
            $table->string('status')->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status']);
            $table->index(['request_date', 'priority']);
        });

        Schema::create('purchase_request_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained('purchase_requests')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete()->cascadeOnUpdate();
            $table->integer('requested_quantity');
            $table->decimal('estimated_cost', 15, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_orders', function (Blueprint $table): void {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('request_id')->nullable()->constrained('purchase_requests')->nullOnDelete()->cascadeOnUpdate();
            $table->date('po_date');
            $table->date('expected_delivery_date')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('status')->default('draft');
            $table->foreignId('prepared_by')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['supplier_id', 'status']);
            $table->index(['branch_id', 'status']);
        });

        Schema::create('purchase_order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete()->cascadeOnUpdate();
            $table->integer('quantity_ordered');
            $table->integer('quantity_received')->default(0);
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('receiving_reports', function (Blueprint $table): void {
            $table->id();
            $table->string('receiving_number')->unique();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete()->cascadeOnUpdate();
            $table->date('received_date');
            $table->string('delivery_receipt_number')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('attachment_path')->nullable();
            $table->foreignId('received_by')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('status')->default('draft');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status']);
            $table->index(['supplier_id', 'received_date']);
        });

        Schema::create('receiving_report_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('receiving_report_id')->constrained('receiving_reports')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete()->cascadeOnUpdate();
            $table->integer('quantity_received');
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->json('serialized_entries')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('supplier_payables', function (Blueprint $table): void {
            $table->id();
            $table->string('payable_number')->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('receiving_report_id')->nullable()->constrained('receiving_reports')->nullOnDelete()->cascadeOnUpdate();
            $table->string('invoice_number')->nullable();
            $table->date('payable_date');
            $table->date('due_date')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('balance_amount', 15, 2)->default(0);
            $table->string('payment_status')->default('unpaid');
            $table->string('status')->default('active');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['supplier_id', 'payment_status']);
            $table->index(['branch_id', 'due_date']);
        });

        Schema::create('supplier_payments', function (Blueprint $table): void {
            $table->id();
            $table->string('payment_number')->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('payable_id')->constrained('supplier_payables')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete()->cascadeOnUpdate();
            $table->date('payment_date');
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete()->cascadeOnUpdate();
            $table->string('reference_number')->nullable();
            $table->decimal('amount_paid', 15, 2);
            $table->string('proof_file')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('paid_by')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();

            $table->index(['supplier_id', 'payment_date']);
        });

        Schema::create('office_supply_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('category_code')->unique();
            $table->string('category_name');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('office_supplies', function (Blueprint $table): void {
            $table->id();
            $table->string('supply_code')->unique();
            $table->string('supply_name');
            $table->foreignId('category_id')->constrained('office_supply_categories')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('unit');
            $table->integer('reorder_level')->default(0);
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['category_id', 'status']);
        });

        Schema::create('office_supply_inventories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('office_supply_id')->constrained('office_supplies')->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer('quantity_on_hand')->default(0);
            $table->integer('quantity_available')->default(0);
            $table->integer('reorder_level')->default(0);
            $table->timestamps();

            $table->unique(['branch_id', 'office_supply_id']);
        });

        Schema::create('office_supply_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('office_supply_id')->constrained('office_supplies')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('movement_type');
            $table->integer('quantity_in')->default(0);
            $table->integer('quantity_out')->default(0);
            $table->integer('running_balance')->default(0);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });

        Schema::create('office_supply_issuances', function (Blueprint $table): void {
            $table->id();
            $table->string('issuance_number')->unique();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('issued_to')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->date('issue_date');
            $table->string('purpose');
            $table->string('status')->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status']);
        });

        Schema::create('office_supply_issuance_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('issuance_id')->constrained('office_supply_issuances')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('office_supply_id')->constrained('office_supplies')->restrictOnDelete()->cascadeOnUpdate();
            $table->integer('quantity_requested');
            $table->integer('quantity_issued')->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('staff_accountabilities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('office_supply_id')->constrained('office_supplies')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('issuance_item_id')->nullable()->constrained('office_supply_issuance_items')->nullOnDelete()->cascadeOnUpdate();
            $table->integer('quantity_issued');
            $table->date('date_issued');
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->string('purpose')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'date_issued']);
            $table->index(['branch_id', 'date_issued']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_accountabilities');
        Schema::dropIfExists('office_supply_issuance_items');
        Schema::dropIfExists('office_supply_issuances');
        Schema::dropIfExists('office_supply_movements');
        Schema::dropIfExists('office_supply_inventories');
        Schema::dropIfExists('office_supplies');
        Schema::dropIfExists('office_supply_categories');
        Schema::dropIfExists('supplier_payments');
        Schema::dropIfExists('supplier_payables');
        Schema::dropIfExists('receiving_report_items');
        Schema::dropIfExists('receiving_reports');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('purchase_request_items');
        Schema::dropIfExists('purchase_requests');
        Schema::dropIfExists('suppliers');
    }
};
