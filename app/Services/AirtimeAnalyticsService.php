<?php

namespace App\Services;

use App\Models\AirtimeCommission;
use App\Models\AirtimeTransaction;
use App\Models\AirtimeWallet;

class AirtimeAnalyticsService
{
    public function summary(array $filters = []): array
    {
        $transactions = AirtimeTransaction::query();
        $transactions->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId));
        $transactions->when($filters['provider_id'] ?? null, fn ($q, $providerId) => $q->where('provider_id', $providerId));
        $transactions->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('processed_at', '>=', $date));
        $transactions->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('processed_at', '<=', $date));

        $commissions = AirtimeCommission::query();
        $commissions->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId));
        $commissions->when($filters['provider_id'] ?? null, fn ($q, $providerId) => $q->where('provider_id', $providerId));

        $wallets = AirtimeWallet::query();
        $wallets->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId));

        return [
            'cards' => [
                'airtime_sales' => (float) (clone $transactions)->sum('load_amount'),
                'commissions' => (float) (clone $commissions)->sum('commission_amount'),
                'wallet_balance' => (float) (clone $wallets)->sum('current_balance'),
                'failed_or_reversed' => (int) (clone $transactions)->whereIn('transaction_status', ['failed', 'reversed'])->count(),
            ],
            'charts' => [
                'sales_per_provider' => (clone $transactions)->selectRaw('provider_id as label, SUM(load_amount) as value')->groupBy('provider_id')->get(),
                'wallet_balance_per_branch' => (clone $wallets)->selectRaw('branch_id as label, SUM(current_balance) as value')->groupBy('branch_id')->get(),
                'commission_trend' => (clone $commissions)->selectRaw('DATE(created_at) as label, SUM(commission_amount) as value')->groupByRaw('DATE(created_at)')->orderBy('label')->limit(31)->get(),
            ],
            'tables' => [
                'suspicious_transactions' => (clone $transactions)->whereIn('transaction_status', ['failed', 'reversed'])->latest('id')->limit(15)->get(),
            ],
        ];
    }
}
