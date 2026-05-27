<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->string('customer_code')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();
            $table->string('full_name');
            $table->string('mobile_number');
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('gender')->nullable();
            $table->string('customer_type')->default('walk_in');
            $table->string('status')->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['mobile_number', 'status']);
            $table->index(['full_name']);
        });

        Schema::table('sales', function (Blueprint $table): void {
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
        });

        Schema::create('customer_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->text('note');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('warranty_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('rule_code')->unique();
            $table->string('rule_name');
            $table->foreignId('product_category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->unsignedInteger('warranty_duration');
            $table->string('warranty_duration_type');
            $table->text('warranty_coverage')->nullable();
            $table->text('exclusions')->nullable();
            $table->boolean('requires_imei')->default(false);
            $table->string('status')->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('warranties', function (Blueprint $table): void {
            $table->id();
            $table->string('warranty_number')->unique();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('sale_item_id')->constrained('sale_items')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('imei_id')->nullable()->constrained('product_imeis')->nullOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->date('warranty_start_date');
            $table->date('warranty_end_date');
            $table->string('warranty_status')->default('active');
            $table->text('coverage_details')->nullable();
            $table->text('exclusions')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('sale_item_id');
            $table->index(['branch_id', 'warranty_status']);
            $table->index(['warranty_end_date']);
        });

        Schema::create('warranty_claims', function (Blueprint $table): void {
            $table->id();
            $table->string('claim_number')->unique();
            $table->foreignId('warranty_id')->constrained('warranties')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->date('claim_date');
            $table->text('issue_description');
            $table->text('product_condition')->nullable();
            $table->string('claim_status')->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('resolution_type')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['branch_id', 'claim_status']);
        });

        Schema::create('warranty_claim_attachments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('claim_id')->constrained('warranty_claims')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type');
            $table->unsignedBigInteger('file_size');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('warranty_claim_status_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('claim_id')->constrained('warranty_claims')->cascadeOnDelete();
            $table->string('status');
            $table->text('remarks')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('warranty_repairs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('claim_id')->constrained('warranty_claims')->cascadeOnDelete();
            $table->text('repair_details')->nullable();
            $table->string('technician_name')->nullable();
            $table->date('repair_start_date')->nullable();
            $table->date('repair_end_date')->nullable();
            $table->string('repair_status')->default('pending');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique('claim_id');
        });

        Schema::create('warranty_replacements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('claim_id')->constrained('warranty_claims')->cascadeOnDelete();
            $table->foreignId('old_product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('old_imei_id')->nullable()->constrained('product_imeis')->nullOnDelete();
            $table->foreignId('replacement_product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('replacement_imei_id')->nullable()->constrained('product_imeis')->nullOnDelete();
            $table->date('replacement_date');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique('claim_id');
        });

        Schema::create('customer_notification_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->boolean('notify_warranty_expiry')->default(true);
            $table->boolean('notify_claim_updates')->default(true);
            $table->boolean('notify_promotions')->default(false);
            $table->timestamps();

            $table->unique('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_notification_preferences');
        Schema::dropIfExists('warranty_replacements');
        Schema::dropIfExists('warranty_repairs');
        Schema::dropIfExists('warranty_claim_status_logs');
        Schema::dropIfExists('warranty_claim_attachments');
        Schema::dropIfExists('warranty_claims');
        Schema::dropIfExists('warranties');
        Schema::dropIfExists('warranty_rules');
        Schema::dropIfExists('customer_notes');

        Schema::table('sales', function (Blueprint $table): void {
            $table->dropForeign(['customer_id']);
        });

        Schema::dropIfExists('customers');
    }
};
