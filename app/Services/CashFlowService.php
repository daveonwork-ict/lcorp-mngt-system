<?php

namespace App\Services;

use App\Models\CashIn;
use App\Models\CashOut;
use App\Models\CashVariance;
use App\Models\DailyClosing;
use App\Models\Expense;
use App\Models\FundTransfer;

class CashFlowService
{
    public function __construct(
        private readonly CashInService $cashInService,
        private readonly CashOutService $cashOutService,
    ) {
    }

    public function dashboard(?int $branchId = null): array
    {
        $this->cashInService->syncPreparedCashIns();
        $this->cashOutService->syncAirtimeFundingCashOuts();

        $today = now()->toDateString();

        $cashInQuery = CashIn::query()->whereDate('received_at', $today);
        $cashOutQuery = CashOut::query()->whereDate('released_at', $today);
        $expenseQuery = Expense::query()->whereDate('expense_date', $today);

        if ($branchId) {
            $cashInQuery->where('branch_id', $branchId);
            $cashOutQuery->where('branch_id', $branchId);
            $expenseQuery->where('branch_id', $branchId);
        }

        $todayCashIn = (float) $cashInQuery->sum('amount');
        $todayCashOut = (float) $cashOutQuery->sum('amount');

        return [
            'cards' => [
                'today_cash_in' => $todayCashIn,
                'today_cash_out' => $todayCashOut,
                'expected_cash' => round($todayCashIn - $todayCashOut, 2),
                'actual_cash' => (float) DailyClosing::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->whereDate('closing_date', $today)->sum('actual_cash'),
                'cash_variance' => (float) CashVariance::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->whereDate('created_at', $today)->sum('variance_amount'),
                'pending_expenses' => (int) (clone $expenseQuery)->where('status', 'pending')->count(),
                'approved_expenses' => (int) (clone $expenseQuery)->where('status', 'approved')->count(),
                'branch_cash_position' => round($todayCashIn - $todayCashOut, 2),
                'pending_daily_closing' => (int) DailyClosing::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->whereDate('closing_date', $today)->whereIn('status', ['draft', 'submitted'])->count(),
                'fund_transfers' => (int) FundTransfer::query()->when($branchId, fn ($q) => $q->where('source_branch_id', $branchId)->orWhere('destination_branch_id', $branchId))->whereDate('created_at', $today)->count(),
            ],
            'charts' => [
                'cash_in_out' => $this->dailyCashInOut($branchId),
                'expense_trend' => $this->expenseTrend($branchId),
                'variance_trend' => $this->varianceTrend($branchId),
            ],
            'recent' => [
                'cash_ins' => CashIn::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->latest('id')->limit(10)->get(),
                'cash_outs' => CashOut::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->latest('id')->limit(10)->get(),
                'pending_expenses' => Expense::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->where('status', 'pending')->latest('id')->limit(10)->get(),
                'pending_closings' => DailyClosing::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->whereIn('status', ['draft', 'submitted'])->latest('id')->limit(10)->get(),
                'variances' => CashVariance::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->latest('id')->limit(10)->get(),
            ],
        ];
    }

    private function dailyCashInOut(?int $branchId = null): array
    {
        $labels = [];
        $ins = [];
        $outs = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $labels[] = now()->subDays($i)->format('D');
            $ins[] = (float) CashIn::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->whereDate('received_at', $date)->sum('amount');
            $outs[] = (float) CashOut::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->whereDate('released_at', $date)->sum('amount');
        }

        return ['labels' => $labels, 'cash_in' => $ins, 'cash_out' => $outs];
    }

    private function expenseTrend(?int $branchId = null): array
    {
        $labels = [];
        $values = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $labels[] = now()->subDays($i)->format('D');
            $values[] = (float) Expense::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->whereDate('expense_date', $date)->where('status', 'approved')->sum('amount');
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function varianceTrend(?int $branchId = null): array
    {
        $labels = [];
        $values = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $labels[] = now()->subDays($i)->format('D');
            $values[] = (float) CashVariance::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->whereDate('created_at', $date)->sum('variance_amount');
        }

        return ['labels' => $labels, 'values' => $values];
    }
}
