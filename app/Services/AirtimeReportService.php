<?php

namespace App\Services;

use App\Models\AirtimeCommission;
use App\Models\AirtimeTransaction;
use App\Models\AirtimeWallet;
use App\Models\AirtimeWalletFunding;
use App\Models\AirtimeWalletLedger;

class AirtimeReportService
{
    public function transactions(array $filters = [])
    {
        $allowedBranchIds = $this->allowedBranchIds();

        return AirtimeTransaction::query()
            ->with(['branch', 'provider', 'wallet', 'cashier', 'paymentMethod'])
            ->when($allowedBranchIds !== null, fn ($q) => $q->whereIn('branch_id', $allowedBranchIds))
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('processed_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('processed_at', '<=', $date))
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['provider_id'] ?? null, fn ($q, $providerId) => $q->where('provider_id', $providerId))
            ->when($filters['cashier_id'] ?? null, fn ($q, $cashierId) => $q->where('cashier_id', $cashierId))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('transaction_status', $status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function walletBalances(array $filters = [])
    {
        $allowedBranchIds = $this->allowedBranchIds();

        return AirtimeWallet::query()
            ->with(['branch', 'provider'])
            ->when($allowedBranchIds !== null, fn ($q) => $q->whereIn('branch_id', $allowedBranchIds))
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['provider_id'] ?? null, fn ($q, $providerId) => $q->where('provider_id', $providerId))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function fundings(array $filters = [])
    {
        $allowedBranchIds = $this->allowedBranchIds();

        return AirtimeWalletFunding::query()
            ->with(['wallet', 'branch', 'provider', 'requester', 'approver'])
            ->when($allowedBranchIds !== null, fn ($q) => $q->whereIn('branch_id', $allowedBranchIds))
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('funding_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('funding_date', '<=', $date))
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['provider_id'] ?? null, fn ($q, $providerId) => $q->where('provider_id', $providerId))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function ledgers(array $filters = [])
    {
        $allowedBranchIds = $this->allowedBranchIds();

        return AirtimeWalletLedger::query()
            ->with(['wallet', 'branch', 'provider'])
            ->when($allowedBranchIds !== null, fn ($q) => $q->whereIn('branch_id', $allowedBranchIds))
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['provider_id'] ?? null, fn ($q, $providerId) => $q->where('provider_id', $providerId))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function commissions(array $filters = [])
    {
        $allowedBranchIds = $this->allowedBranchIds();

        return AirtimeCommission::query()
            ->with(['transaction', 'provider', 'branch'])
            ->when($allowedBranchIds !== null, fn ($q) => $q->whereIn('branch_id', $allowedBranchIds))
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['provider_id'] ?? null, fn ($q, $providerId) => $q->where('provider_id', $providerId))
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
