<?php

namespace App\Services;

use App\Models\AirtimeWallet;
use App\Models\CashVariance;
use App\Models\DailyClosing;
use App\Models\Expense;
use App\Models\InventoryAlert;
use App\Models\WarrantyClaim;
use Illuminate\Support\Collection;

class KPIAlertService
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public function currentAlerts(?int $branchId = null): Collection
    {
        $alerts = collect();

        $missingClosings = DailyClosing::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereDate('closing_date', today())
            ->whereIn('status', ['open', 'submitted'])
            ->count();

        if ($missingClosings > 0) {
            $alerts->push(['type' => 'missing_daily_closing', 'severity' => 'high', 'message' => $missingClosings.' daily closing entries still open.']);
        }

        $openVariance = CashVariance::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('resolution_status', 'open')
            ->count();

        if ($openVariance > 0) {
            $alerts->push(['type' => 'high_cash_variance', 'severity' => 'high', 'message' => $openVariance.' unresolved cash variances require action.']);
        }

        $criticalWallets = AirtimeWallet::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereColumn('current_balance', '<=', 'low_balance_threshold')
            ->count();

        if ($criticalWallets > 0) {
            $alerts->push(['type' => 'wallet_balance_critical', 'severity' => 'medium', 'message' => $criticalWallets.' airtime wallets are below threshold.']);
        }

        $criticalStock = InventoryAlert::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('is_resolved', false)
            ->where('severity', 'high')
            ->count();

        if ($criticalStock > 0) {
            $alerts->push(['type' => 'critical_low_stock', 'severity' => 'high', 'message' => $criticalStock.' high severity inventory alerts are unresolved.']);
        }

        $warrantyBacklog = WarrantyClaim::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereIn('claim_status', ['pending', 'under_review'])
            ->count();

        if ($warrantyBacklog > 0) {
            $alerts->push(['type' => 'unresolved_warranty_claim', 'severity' => 'medium', 'message' => $warrantyBacklog.' warranty claims are still pending review.']);
        }

        $expenseSpike = Expense::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereDate('expense_date', '>=', now()->subDays(7)->toDateString())
            ->sum('amount');

        $priorExpense = Expense::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereBetween('expense_date', [now()->subDays(14)->toDateString(), now()->subDays(8)->toDateString()])
            ->sum('amount');

        if ($priorExpense > 0 && $expenseSpike > ($priorExpense * 1.35)) {
            $alerts->push(['type' => 'high_expense_spike', 'severity' => 'medium', 'message' => 'Expense outflow increased by more than 35% versus prior period.']);
        }

        return $alerts;
    }

    public function notify(array $alerts, ?int $branchId = null): void
    {
        foreach ($alerts as $alert) {
            $this->notificationService->create(
                null,
                $branchId,
                'KPI Alert',
                $alert['message'],
                'kpi_alert',
                ['type' => $alert['type'], 'severity' => $alert['severity']]
            );
        }
    }
}
