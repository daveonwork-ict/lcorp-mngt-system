<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table): void {
            $table->id();
            $table->string('position_code')->unique();
            $table->string('position_name');
            $table->string('department')->nullable();
            $table->string('salary_type')->default('monthly');
            $table->decimal('default_salary_rate', 14, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('employee_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->date('birthdate')->nullable();
            $table->string('gender')->nullable();
            $table->string('civil_status')->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_number')->nullable();
            $table->date('employment_date')->nullable();
            $table->string('employment_type')->default('regular');
            $table->string('employment_status')->default('active');
            $table->string('salary_type')->default('monthly');
            $table->decimal('salary_rate', 14, 2)->default(0);
            $table->timestamps();

            $table->unique('user_id');
            $table->index(['branch_id', 'employment_status'], 'emp_profiles_branch_status_idx');
        });

        Schema::create('employee_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained('employee_profiles')->cascadeOnDelete();
            $table->string('document_type');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('employee_position_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained('employee_profiles')->cascadeOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->decimal('salary_rate', 14, 2)->default(0);
            $table->date('effective_start_date');
            $table->date('effective_end_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['employee_profile_id', 'effective_start_date'], 'ep_hist_profile_start_idx');
        });

        Schema::create('employee_employment_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained('employee_profiles')->cascadeOnDelete();
            $table->string('employment_status');
            $table->string('employment_type');
            $table->date('effective_start_date');
            $table->date('effective_end_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['employee_profile_id', 'effective_start_date'], 'ee_hist_profile_start_idx');
        });

        Schema::create('employee_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->date('schedule_date');
            $table->string('schedule_type')->default('fixed');
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->boolean('is_rest_day')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'schedule_date']);
            $table->index(['branch_id', 'schedule_date']);
        });

        Schema::create('attendance_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->foreignId('schedule_id')->nullable()->constrained('employee_schedules')->nullOnDelete();
            $table->timestamp('time_in')->nullable();
            $table->timestamp('time_out')->nullable();
            $table->string('selfie_time_in_path')->nullable();
            $table->string('selfie_time_out_path')->nullable();
            $table->decimal('gps_latitude_in', 10, 7)->nullable();
            $table->decimal('gps_longitude_in', 10, 7)->nullable();
            $table->decimal('gps_latitude_out', 10, 7)->nullable();
            $table->decimal('gps_longitude_out', 10, 7)->nullable();
            $table->json('device_info_in')->nullable();
            $table->json('device_info_out')->nullable();
            $table->string('ip_address_in', 45)->nullable();
            $table->string('ip_address_out', 45)->nullable();
            $table->unsignedInteger('late_minutes')->default(0);
            $table->unsignedInteger('undertime_minutes')->default(0);
            $table->unsignedInteger('overtime_minutes')->default(0);
            $table->string('attendance_status')->default('present');
            $table->timestamps();

            $table->unique(['user_id', 'attendance_date']);
            $table->index(['branch_id', 'attendance_date']);
        });

        Schema::create('leave_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('leave_type');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_days', 6, 2)->default(0);
            $table->text('reason');
            $table->string('status')->default('pending_manager');
            $table->foreignId('manager_reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('manager_reviewed_at')->nullable();
            $table->foreignId('hr_reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('hr_reviewed_at')->nullable();
            $table->text('final_remarks')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status']);
            $table->index(['user_id', 'start_date']);
        });

        Schema::create('leave_balances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('leave_year');
            $table->string('leave_type');
            $table->decimal('credits', 6, 2)->default(0);
            $table->decimal('used', 6, 2)->default(0);
            $table->decimal('balance', 6, 2)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'leave_year', 'leave_type']);
        });

        Schema::create('overtime_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->date('overtime_date');
            $table->decimal('hours', 6, 2);
            $table->text('reason');
            $table->string('status')->default('pending_manager');
            $table->foreignId('manager_reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('manager_reviewed_at')->nullable();
            $table->foreignId('hr_reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('hr_reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status']);
            $table->index(['user_id', 'overtime_date']);
        });

        Schema::create('payroll_periods', function (Blueprint $table): void {
            $table->id();
            $table->string('period_code')->unique();
            $table->string('payroll_period_type')->default('semi_monthly');
            $table->date('period_start');
            $table->date('period_end');
            $table->string('status')->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['period_start', 'period_end']);
        });

        Schema::create('payroll_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payroll_period_id')->constrained('payroll_periods')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('status')->default('draft');
            $table->decimal('total_gross_pay', 14, 2)->default(0);
            $table->decimal('total_deductions', 14, 2)->default(0);
            $table->decimal('total_net_pay', 14, 2)->default(0);
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('hr_reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('hr_reviewed_at')->nullable();
            $table->foreignId('manager_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('manager_approved_at')->nullable();
            $table->foreignId('owner_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('owner_approved_at')->nullable();
            $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'branch_id']);
        });

        Schema::create('payroll_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained('payroll_runs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->decimal('basic_pay', 14, 2)->default(0);
            $table->decimal('overtime_pay', 14, 2)->default(0);
            $table->decimal('allowances', 14, 2)->default(0);
            $table->decimal('holiday_pay', 14, 2)->default(0);
            $table->decimal('night_differential_pay', 14, 2)->default(0);
            $table->decimal('incentives', 14, 2)->default(0);
            $table->decimal('gross_pay', 14, 2)->default(0);
            $table->decimal('sss_deduction', 14, 2)->default(0);
            $table->decimal('philhealth_deduction', 14, 2)->default(0);
            $table->decimal('pagibig_deduction', 14, 2)->default(0);
            $table->decimal('withholding_tax_deduction', 14, 2)->default(0);
            $table->decimal('loan_deduction', 14, 2)->default(0);
            $table->decimal('cash_advance_deduction', 14, 2)->default(0);
            $table->decimal('other_deduction', 14, 2)->default(0);
            $table->decimal('total_deductions', 14, 2)->default(0);
            $table->decimal('net_pay', 14, 2)->default(0);
            $table->string('status')->default('draft');
            $table->json('computation_snapshot')->nullable();
            $table->timestamps();

            $table->unique(['payroll_run_id', 'user_id']);
            $table->index(['branch_id', 'status']);
        });

        Schema::create('sss_contribution_tables', function (Blueprint $table): void {
            $table->id();
            $table->date('effective_date');
            $table->decimal('salary_from', 12, 2);
            $table->decimal('salary_to', 12, 2);
            $table->decimal('msc', 12, 2);
            $table->decimal('employer_share', 12, 2);
            $table->decimal('employee_share', 12, 2);
            $table->timestamps();

            $table->index('effective_date');
        });

        Schema::create('philhealth_contribution_tables', function (Blueprint $table): void {
            $table->id();
            $table->date('effective_date');
            $table->decimal('salary_from', 12, 2);
            $table->decimal('salary_to', 12, 2);
            $table->decimal('premium_rate', 8, 4);
            $table->decimal('employer_share', 12, 2);
            $table->decimal('employee_share', 12, 2);
            $table->timestamps();

            $table->index('effective_date');
        });

        Schema::create('pagibig_contribution_tables', function (Blueprint $table): void {
            $table->id();
            $table->date('effective_date');
            $table->decimal('salary_from', 12, 2);
            $table->decimal('salary_to', 12, 2);
            $table->decimal('employee_rate', 8, 4);
            $table->decimal('employer_rate', 8, 4);
            $table->decimal('employee_share', 12, 2);
            $table->decimal('employer_share', 12, 2);
            $table->timestamps();

            $table->index('effective_date');
        });

        Schema::create('withholding_tax_tables', function (Blueprint $table): void {
            $table->id();
            $table->date('effective_date');
            $table->string('payroll_period_type')->default('semi_monthly');
            $table->decimal('taxable_income_from', 14, 2);
            $table->decimal('taxable_income_to', 14, 2);
            $table->decimal('base_tax', 14, 2)->default(0);
            $table->decimal('excess_over', 14, 2)->default(0);
            $table->decimal('tax_rate', 8, 4)->default(0);
            $table->timestamps();

            $table->index(['effective_date', 'payroll_period_type']);
        });

        Schema::create('cash_advances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->decimal('remaining_balance', 14, 2);
            $table->date('request_date');
            $table->text('reason')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status']);
        });

        Schema::create('employee_loans', function (Blueprint $table): void {
            $table->id();
            $table->string('loan_number')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('loan_type');
            $table->decimal('principal_amount', 14, 2);
            $table->decimal('interest_rate', 8, 4)->default(0);
            $table->decimal('installment_amount', 14, 2)->default(0);
            $table->unsignedInteger('term_months')->default(0);
            $table->decimal('remaining_balance', 14, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('maturity_date')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status']);
        });

        Schema::create('loan_installments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('employee_loan_id')->constrained('employee_loans')->cascadeOnDelete();
            $table->foreignId('payroll_item_id')->nullable()->constrained('payroll_items')->nullOnDelete();
            $table->date('due_date');
            $table->decimal('amount_due', 14, 2);
            $table->decimal('amount_paid', 14, 2)->default(0);
            $table->timestamp('paid_at')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->index(['employee_loan_id', 'due_date']);
        });

        Schema::create('payslips', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payroll_item_id')->constrained('payroll_items')->cascadeOnDelete();
            $table->string('payslip_number')->unique();
            $table->string('file_path')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->unique('payroll_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('loan_installments');
        Schema::dropIfExists('employee_loans');
        Schema::dropIfExists('cash_advances');
        Schema::dropIfExists('withholding_tax_tables');
        Schema::dropIfExists('pagibig_contribution_tables');
        Schema::dropIfExists('philhealth_contribution_tables');
        Schema::dropIfExists('sss_contribution_tables');
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payroll_runs');
        Schema::dropIfExists('payroll_periods');
        Schema::dropIfExists('overtime_requests');
        Schema::dropIfExists('leave_balances');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('attendance_logs');
        Schema::dropIfExists('employee_schedules');
        Schema::dropIfExists('employee_employment_histories');
        Schema::dropIfExists('employee_position_histories');
        Schema::dropIfExists('employee_documents');
        Schema::dropIfExists('employee_profiles');
        Schema::dropIfExists('positions');
    }
};
