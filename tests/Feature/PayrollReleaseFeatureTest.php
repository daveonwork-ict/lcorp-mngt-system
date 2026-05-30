<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\CashAdvance;
use App\Models\EmployeeLoan;
use App\Models\LoanInstallment;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Models\Role;
use App\Models\User;
use App\Services\PayrollService;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PayrollReleaseFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RolesSeeder::class,
            BranchSeeder::class,
        ]);
    }

    public function test_releasing_payroll_applies_deductions_updates_installments_and_generates_payslip(): void
    {
        Storage::fake();

        $branch = Branch::query()->where('code', 'MAIN')->firstOrFail();
        $role = Role::query()->where('code', 'super_admin')->firstOrFail();

        $actor = User::factory()->create([
            'role_id' => $role->id,
            'primary_branch_id' => $branch->id,
            'status' => 'active',
            'is_active' => true,
        ]);

        $employee = User::factory()->create([
            'role_id' => $role->id,
            'primary_branch_id' => $branch->id,
            'status' => 'active',
            'is_active' => true,
        ]);

        $period = PayrollPeriod::query()->create([
            'period_code' => 'FT-'.now()->format('YmdHis'),
            'payroll_period_type' => 'semi_monthly',
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'status' => 'open',
            'created_by' => $actor->id,
        ]);

        $run = PayrollRun::query()->create([
            'payroll_period_id' => $period->id,
            'branch_id' => $branch->id,
            'status' => 'approved',
            'total_gross_pay' => 15000,
            'total_deductions' => 1100,
            'total_net_pay' => 13900,
            'generated_by' => $actor->id,
        ]);

        $item = PayrollItem::query()->create([
            'payroll_run_id' => $run->id,
            'user_id' => $employee->id,
            'branch_id' => $branch->id,
            'basic_pay' => 15000,
            'gross_pay' => 15000,
            'loan_deduction' => 500,
            'cash_advance_deduction' => 600,
            'total_deductions' => 1100,
            'net_pay' => 13900,
            'status' => 'draft',
        ]);

        $loan = EmployeeLoan::query()->create([
            'loan_number' => 'LN-TEST-001',
            'user_id' => $employee->id,
            'branch_id' => $branch->id,
            'loan_type' => 'company',
            'principal_amount' => 1000,
            'interest_rate' => 0,
            'installment_amount' => 500,
            'term_months' => 2,
            'remaining_balance' => 1000,
            'start_date' => now()->toDateString(),
            'status' => 'active',
        ]);

        $installment = LoanInstallment::query()->create([
            'employee_loan_id' => $loan->id,
            'due_date' => now()->toDateString(),
            'amount_due' => 500,
            'amount_paid' => 0,
            'status' => 'pending',
        ]);

        $advance = CashAdvance::query()->create([
            'user_id' => $employee->id,
            'branch_id' => $branch->id,
            'amount' => 600,
            'remaining_balance' => 600,
            'request_date' => now()->toDateString(),
            'status' => 'approved',
        ]);

        app(PayrollService::class)->release($run->fresh(), $actor);

        $run->refresh();
        $item->refresh();
        $loan->refresh();
        $installment->refresh();
        $advance->refresh();

        $this->assertSame('released', $run->status);
        $this->assertSame('released', $item->status);

        $this->assertSame(500.0, (float) $loan->remaining_balance);
        $this->assertSame($item->id, $installment->payroll_item_id);
        $this->assertSame('paid', $installment->status);
        $this->assertSame(500.0, (float) $installment->amount_paid);

        $this->assertSame(0.0, (float) $advance->remaining_balance);
        $this->assertSame('paid', $advance->status);

        $payslip = Payslip::query()->where('payroll_item_id', $item->id)->first();

        $this->assertNotNull($payslip);
        $this->assertSame($actor->id, $payslip->generated_by);
        Storage::assertExists($payslip->file_path);
    }
}
