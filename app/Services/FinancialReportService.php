<?php

namespace App\Services;

use App\Models\CashIn;
use App\Models\CashOut;
use App\Models\CashVariance;
use App\Models\DailyClosing;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\FinancialLedger;
use App\Models\FundTransfer;

class FinancialReportService
{
    public function data(array $filters = []): array
    {
        return [
            'cash_ins' => $this->cashIns($filters),
            'cash_outs' => $this->cashOuts($filters),
            'expenses' => $this->expenses($filters),
            'expense_categories' => $this->expenseCategories($filters),
            'closings' => $this->closings($filters),
            'variances' => $this->variances($filters),
            'transfers' => $this->transfers($filters),
            'ledgers' => $this->ledgers($filters),
        ];
    }

    public function cashIns(array $filters = [])
    {
        $allowedBranchIds = $this->allowedBranchIds();

        return CashIn::query()
            ->with(['branch', 'receiver'])
            ->when($allowedBranchIds !== null, fn ($q) => $q->whereIn('branch_id', $allowedBranchIds))
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('received_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('received_at', '<=', $date))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function cashOuts(array $filters = [])
    {
        $allowedBranchIds = $this->allowedBranchIds();

        return CashOut::query()
            ->with(['branch', 'releaser'])
            ->when($allowedBranchIds !== null, fn ($q) => $q->whereIn('branch_id', $allowedBranchIds))
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('released_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('released_at', '<=', $date))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function expenses(array $filters = [])
    {
        $allowedBranchIds = $this->allowedBranchIds();

        return Expense::query()
            ->with(['branch', 'category', 'submitter'])
            ->when($allowedBranchIds !== null, fn ($q) => $q->whereIn('branch_id', $allowedBranchIds))
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['category_id'] ?? null, fn ($q, $categoryId) => $q->where('category_id', $categoryId))
            ->when($filters['user_id'] ?? null, fn ($q, $userId) => $q->where('submitted_by', $userId))
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('expense_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('expense_date', '<=', $date))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function expenseCategories(array $filters = [])
    {
        return ExpenseCategory::query()
            ->withCount('expenses')
            ->orderBy('category_name')
            ->get();
    }

    public function closings(array $filters = [])
    {
        $allowedBranchIds = $this->allowedBranchIds();

        return DailyClosing::query()
            ->with(['branch', 'cashier'])
            ->when($allowedBranchIds !== null, fn ($q) => $q->whereIn('branch_id', $allowedBranchIds))
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('closing_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('closing_date', '<=', $date))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function variances(array $filters = [])
    {
        $allowedBranchIds = $this->allowedBranchIds();

        return CashVariance::query()
            ->with(['branch', 'cashier'])
            ->when($allowedBranchIds !== null, fn ($q) => $q->whereIn('branch_id', $allowedBranchIds))
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('resolution_status', $status))
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function transfers(array $filters = [])
    {
        $allowedBranchIds = $this->allowedBranchIds();

        return FundTransfer::query()
            ->with(['sourceBranch', 'destinationBranch', 'requester'])
            ->when($allowedBranchIds !== null, fn ($q) => $q->where(function ($scope) use ($allowedBranchIds): void {
                $scope->whereIn('source_branch_id', $allowedBranchIds)->orWhereIn('destination_branch_id', $allowedBranchIds);
            }))
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where(function ($scope) use ($branchId): void {
                $scope->where('source_branch_id', $branchId)->orWhere('destination_branch_id', $branchId);
            }))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function ledgers(array $filters = [])
    {
        $allowedBranchIds = $this->allowedBranchIds();

        return FinancialLedger::query()
            ->with(['branch', 'performer'])
            ->when($allowedBranchIds !== null, fn ($q) => $q->whereIn('branch_id', $allowedBranchIds))
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    private function allowedBranchIds(): ?array
    {
        $user = auth()->user();
        if (! $user || $user->role?->code === config('rms.owner_role_code')) {
            return null;
        }

        return $user->branches()->pluck('branches.id')->all() ?: [-1];
    }
}
