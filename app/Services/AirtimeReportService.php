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
        return AirtimeTransaction::query()
            ->with(['branch', 'provider', 'wallet', 'cashier', 'paymentMethod'])
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
        return AirtimeWallet::query()
            ->with(['branch', 'provider'])
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['provider_id'] ?? null, fn ($q, $providerId) => $q->where('provider_id', $providerId))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }

    public function fundings(array $filters = [])
    {
        return AirtimeWalletFunding::query()
            ->with(['wallet', 'branch', 'provider', 'requester', 'approver'])
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
        return AirtimeWalletLedger::query()
            ->with(['wallet', 'branch', 'provider'])
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
        return AirtimeCommission::query()
            ->with(['transaction', 'provider', 'branch'])
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['provider_id'] ?? null, fn ($q, $providerId) => $q->where('provider_id', $providerId))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }
}
