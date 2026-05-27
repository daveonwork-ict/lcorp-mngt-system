<?php

namespace App\Services;

use App\Models\AirtimeAlert;
use App\Models\AirtimeTransaction;
use App\Models\AirtimeWallet;
use App\Models\AirtimeWalletFunding;
use Illuminate\Support\Carbon;

class AirtimeDashboardService
{
    public function summary(?int $branchId = null): array
    {
        $transactions = AirtimeTransaction::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
        $wallets = AirtimeWallet::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        $todaySales = (float) (clone $transactions)->whereDate('processed_at', Carbon::today())->where('transaction_status', 'successful')->sum('total_amount');
        $monthlySales = (float) (clone $transactions)->whereDate('processed_at', '>=', Carbon::now()->startOfMonth())->where('transaction_status', 'successful')->sum('total_amount');
        $totalCommission = (float) (clone $transactions)->where('transaction_status', 'successful')->sum('commission_amount');
        $walletBalance = (float) (clone $wallets)->sum('current_balance');
        $lowWalletCount = (clone $wallets)->whereColumn('current_balance', '<=', 'low_balance_threshold')->count();
        $pendingFunding = AirtimeWalletFunding::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->where('status', 'pending')->count();
        $failedTransactions = (clone $transactions)->where('transaction_status', 'failed')->count();
        $reversedTransactions = (clone $transactions)->where('transaction_status', 'reversed')->count();

        return [
            'cards' => [
                ['label' => "Today's Load Sales", 'value' => number_format($todaySales, 2)],
                ['label' => 'Monthly Load Sales', 'value' => number_format($monthlySales, 2)],
                ['label' => 'Total Commission', 'value' => number_format($totalCommission, 2)],
                ['label' => 'Total Wallet Balance', 'value' => number_format($walletBalance, 2)],
                ['label' => 'Low Wallet Count', 'value' => $lowWalletCount],
                ['label' => 'Pending Funding Requests', 'value' => $pendingFunding],
                ['label' => 'Failed Transactions', 'value' => $failedTransactions],
                ['label' => 'Reversed Transactions', 'value' => $reversedTransactions],
            ],
            'charts' => [
                'sales_per_provider' => AirtimeTransaction::query()->selectRaw('provider_id, SUM(total_amount) as total_sales')->where('transaction_status', 'successful')->groupBy('provider_id')->get(),
                'sales_per_branch' => AirtimeTransaction::query()->selectRaw('branch_id, SUM(total_amount) as total_sales')->where('transaction_status', 'successful')->groupBy('branch_id')->get(),
                'wallet_balance_per_branch' => AirtimeWallet::query()->selectRaw('branch_id, SUM(current_balance) as total_balance')->groupBy('branch_id')->get(),
                'commission_trend' => AirtimeTransaction::query()->selectRaw('DATE(processed_at) as day, SUM(commission_amount) as total_commission')->where('transaction_status', 'successful')->groupByRaw('DATE(processed_at)')->orderBy('day')->get(),
                'daily_sales_trend' => AirtimeTransaction::query()->selectRaw('DATE(processed_at) as day, SUM(total_amount) as total_sales')->where('transaction_status', 'successful')->groupByRaw('DATE(processed_at)')->orderBy('day')->get(),
            ],
            'tables' => [
                'recent_transactions' => AirtimeTransaction::query()->with(['provider', 'branch'])->latest('id')->limit(10)->get(),
                'low_wallets' => AirtimeWallet::query()->with(['provider', 'branch'])->whereColumn('current_balance', '<=', 'low_balance_threshold')->limit(10)->get(),
                'pending_funding' => AirtimeWalletFunding::query()->with(['wallet', 'branch', 'provider'])->where('status', 'pending')->latest('id')->limit(10)->get(),
                'suspicious' => AirtimeAlert::query()->with(['branch', 'provider'])->where('alert_type', 'suspicious_transaction')->where('is_resolved', false)->latest('id')->limit(10)->get(),
            ],
        ];
    }
}
