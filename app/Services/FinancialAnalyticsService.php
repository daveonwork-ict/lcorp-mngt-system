<?php

namespace App\Services;

use App\Models\CashVariance;
use App\Models\DailyClosing;
use App\Models\Expense;
use App\Models\FundTransfer;

class FinancialAnalyticsService
{
    public function summary(array $filters = []): array
    {
        $expenses = Expense::query();
        $expenses->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId));
        $expenses->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('expense_date', '>=', $date));
        $expenses->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('expense_date', '<=', $date));

        $variances = CashVariance::query()->where('resolution_status', 'open');
        $variances->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId));

        return [
            'cards' => [
                'total_expenses' => (float) (clone $expenses)->sum('amount'),
                'unresolved_variances' => (int) (clone $variances)->count(),
                'pending_closings' => (int) DailyClosing::query()->whereIn('status', ['open', 'submitted'])->count(),
                'pending_transfers' => (int) FundTransfer::query()->whereIn('status', ['pending', 'pending_approval'])->count(),
            ],
            'charts' => [
                'expense_trend' => (clone $expenses)->selectRaw('expense_date as label, SUM(amount) as value')->groupBy('expense_date')->orderBy('label')->limit(31)->get(),
                'expense_by_category' => (clone $expenses)->selectRaw('category_id as label, SUM(amount) as value')->groupBy('category_id')->get(),
                'cash_variance_trend' => CashVariance::query()->selectRaw('DATE(created_at) as label, SUM(variance_amount) as value')->groupByRaw('DATE(created_at)')->orderBy('label')->limit(31)->get(),
            ],
            'tables' => [
                'recent_expenses' => (clone $expenses)->with(['branch', 'category'])->latest('id')->limit(10)->get(),
                'pending_daily_closings' => DailyClosing::query()->with(['branch', 'cashier'])->whereIn('status', ['open', 'submitted'])->latest('id')->limit(10)->get(),
            ],
        ];
    }
}
