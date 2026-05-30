<?php

namespace App\Services;

use App\Models\EmployeeLoan;
use App\Models\LoanInstallment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class LoanService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly BranchAccessService $branchAccessService,
    ) {
    }

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $user = Auth::user();

        return EmployeeLoan::query()
            ->with(['user', 'branch', 'approvedBy', 'releasedBy'])
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($user && ! in_array($user->role?->code, [config('rms.owner_role_code'), 'super_admin', 'branch_manager', 'accounting_staff'], true), fn ($q) => $q->where('user_id', $user->id))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();
    }

    public function create(array $data): EmployeeLoan
    {
        $this->ensureBranchAccess((int) $data['branch_id']);

        $user = Auth::user();

        if ($user && ! in_array($user->role?->code, [config('rms.owner_role_code'), 'super_admin', 'branch_manager', 'accounting_staff'], true)) {
            $data['user_id'] = $user->id;
        }

        $data['loan_number'] = $data['loan_number'] ?? ('LN-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT));
        $data['status'] = 'pending';
        $data['remaining_balance'] = $this->computeTotalLoan((float) $data['principal_amount'], (float) ($data['interest_rate'] ?? 0));

        $loan = EmployeeLoan::query()->create($data);

        $this->generateInstallments($loan);

        $this->auditLogService->record('hr_loans', 'loan_created', [], $loan->toArray(), $loan->branch_id, 'Loan created');

        return $loan;
    }

    public function approve(EmployeeLoan $loan): EmployeeLoan
    {
        $this->ensureBranchAccess((int) $loan->branch_id);

        $before = $loan->toArray();
        $loan->update([
            'status' => 'active',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        $this->auditLogService->record('hr_loans', 'loan_approved', $before, $loan->toArray(), $loan->branch_id, 'Loan approved');

        return $loan;
    }

    public function release(EmployeeLoan $loan): EmployeeLoan
    {
        $this->ensureBranchAccess((int) $loan->branch_id);

        $before = $loan->toArray();
        $loan->update([
            'status' => 'active',
            'released_by' => Auth::id(),
            'released_at' => now(),
        ]);

        $this->auditLogService->record('hr_loans', 'loan_released', $before, $loan->toArray(), $loan->branch_id, 'Loan released');

        return $loan;
    }

    private function generateInstallments(EmployeeLoan $loan): void
    {
        if ((int) $loan->term_months <= 0 || (float) $loan->installment_amount <= 0 || ! $loan->start_date) {
            return;
        }

        LoanInstallment::query()->where('employee_loan_id', $loan->id)->delete();

        $dueDate = $loan->start_date->copy();

        for ($i = 0; $i < (int) $loan->term_months; $i++) {
            LoanInstallment::query()->create([
                'employee_loan_id' => $loan->id,
                'due_date' => $dueDate,
                'amount_due' => $loan->installment_amount,
                'amount_paid' => 0,
                'status' => 'pending',
            ]);

            $dueDate = $dueDate->copy()->addMonth();
        }
    }

    private function computeTotalLoan(float $principal, float $interestRate): float
    {
        return round($principal + ($principal * $interestRate), 2);
    }

    private function ensureBranchAccess(int $branchId): void
    {
        $user = Auth::user();

        if ($user && ! $this->branchAccessService->canAccessBranch($user, $branchId)) {
            abort(403, 'Branch loan access denied.');
        }
    }
}
