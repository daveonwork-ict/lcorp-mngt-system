<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\DashboardPreference;
use App\Models\Sale;
use App\Models\User;

class DashboardAnalyticsService
{
    public function __construct(
        private readonly SalesAnalyticsService $salesAnalytics,
        private readonly InventoryAnalyticsService $inventoryAnalytics,
        private readonly AirtimeAnalyticsService $airtimeAnalytics,
        private readonly FinancialAnalyticsService $financialAnalytics,
        private readonly WarrantyAnalyticsService $warrantyAnalytics,
        private readonly KPIAlertService $kpiAlertService,
    ) {
    }

    public function executive(User $user, array $filters): array
    {
        $sales = $this->salesAnalytics->summary($filters);
        $inventory = $this->inventoryAnalytics->summary($filters);
        $airtime = $this->airtimeAnalytics->summary($filters);
        $financial = $this->financialAnalytics->summary($filters);
        $warranty = $this->warrantyAnalytics->summary($filters);
        $alerts = $this->kpiAlertService->currentAlerts($filters['branch_id'] ?? null);

        $monthlySales = (float) ($sales['cards']['monthly_sales'] ?? 0);
        $monthlyExpenses = (float) ($financial['cards']['total_expenses'] ?? 0);

        $cards = [
            ['label' => "Today's total sales", 'value' => number_format((float) ($sales['cards']['today_sales'] ?? 0), 2), 'tone' => 'info'],
            ['label' => 'Monthly total sales', 'value' => number_format($monthlySales, 2), 'tone' => 'primary'],
            ['label' => 'Total expenses', 'value' => number_format($monthlyExpenses, 2), 'tone' => 'warning'],
            ['label' => 'Net income estimate', 'value' => number_format($monthlySales - $monthlyExpenses, 2), 'tone' => 'success'],
            ['label' => 'Inventory value', 'value' => number_format((float) ($inventory['cards']['inventory_value'] ?? 0), 2), 'tone' => 'secondary'],
            ['label' => 'Low stock items', 'value' => (int) ($inventory['cards']['low_stock'] ?? 0), 'tone' => 'danger'],
            ['label' => 'Total airtime sales', 'value' => number_format((float) ($airtime['cards']['airtime_sales'] ?? 0), 2), 'tone' => 'indigo'],
            ['label' => 'Total airtime commissions', 'value' => number_format((float) ($airtime['cards']['commissions'] ?? 0), 2), 'tone' => 'teal'],
            ['label' => 'Pending warranty claims', 'value' => (int) ($warranty['cards']['pending_claims'] ?? 0), 'tone' => 'orange'],
            ['label' => 'Active announcements', 'value' => Announcement::query()->where('status', 'published')->count(), 'tone' => 'purple'],
            ['label' => 'Unresolved cash variances', 'value' => (int) ($financial['cards']['unresolved_variances'] ?? 0), 'tone' => 'maroon'],
            ['label' => 'Pending approvals', 'value' => (int) ($financial['cards']['pending_closings'] ?? 0), 'tone' => 'olive'],
        ];

        return [
            'cards' => $cards,
            'charts' => [
                'sales_trend' => $sales['charts']['sales_trend'] ?? collect(),
                'sales_per_branch' => $sales['charts']['sales_per_branch'] ?? collect(),
                'sales_by_cashier' => $sales['charts']['sales_by_cashier'] ?? collect(),
                'sales_by_payment_method' => $sales['charts']['sales_by_payment_method'] ?? collect(),
                'inventory_value_per_branch' => $inventory['charts']['inventory_value_per_branch'] ?? collect(),
                'inventory_movement_trend' => $inventory['charts']['inventory_movement_trend'] ?? collect(),
                'airtime_sales_per_provider' => $airtime['charts']['sales_per_provider'] ?? collect(),
                'expense_trend' => $financial['charts']['expense_trend'] ?? collect(),
                'warranty_claims_trend' => $warranty['charts']['claims_trend'] ?? collect(),
            ],
            'tables' => [
                'recent_sales' => $sales['tables']['recent_sales'] ?? collect(),
                'recent_expenses' => $financial['tables']['recent_expenses'] ?? collect(),
                'low_stock_products' => $inventory['tables']['low_stock_products'] ?? collect(),
                'pending_warranty_claims' => $warranty['tables']['pending_warranty_claims'] ?? collect(),
                'pending_daily_closings' => $financial['tables']['pending_daily_closings'] ?? collect(),
                'suspicious_airtime_transactions' => $airtime['tables']['suspicious_transactions'] ?? collect(),
                'top_performing_branches' => Sale::query()->selectRaw('branch_id, SUM(total_amount) as total_sales')->where('sales_status', 'completed')->groupBy('branch_id')->orderByDesc('total_sales')->limit(5)->get(),
                'underperforming_branches' => Sale::query()->selectRaw('branch_id, SUM(total_amount) as total_sales')->where('sales_status', 'completed')->groupBy('branch_id')->orderBy('total_sales')->limit(5)->get(),
            ],
            'kpi_alerts' => $alerts,
            'preferences' => DashboardPreference::query()->firstOrCreate([
                'user_id' => $user->id,
                'dashboard_key' => 'executive',
            ], [
                'preferences' => [
                    'date_range' => '30_days',
                ],
            ]),
        ];
    }

    public function branch(User $user, int $branchId, array $filters): array
    {
        $filters['branch_id'] = $branchId;

        $sales = $this->salesAnalytics->summary($filters);
        $inventory = $this->inventoryAnalytics->summary($filters);
        $airtime = $this->airtimeAnalytics->summary($filters);
        $financial = $this->financialAnalytics->summary($filters);
        $alerts = $this->kpiAlertService->currentAlerts($branchId);

        return [
            'cards' => [
                ['label' => 'Branch today sales', 'value' => number_format((float) ($sales['cards']['today_sales'] ?? 0), 2), 'tone' => 'info'],
                ['label' => 'Branch monthly sales', 'value' => number_format((float) ($sales['cards']['monthly_sales'] ?? 0), 2), 'tone' => 'primary'],
                ['label' => 'Branch expenses', 'value' => number_format((float) ($financial['cards']['total_expenses'] ?? 0), 2), 'tone' => 'warning'],
                ['label' => 'Branch low stock items', 'value' => (int) ($inventory['cards']['low_stock'] ?? 0), 'tone' => 'danger'],
                ['label' => 'Branch airtime sales', 'value' => number_format((float) ($airtime['cards']['airtime_sales'] ?? 0), 2), 'tone' => 'indigo'],
                ['label' => 'Branch unresolved variance', 'value' => (int) ($financial['cards']['unresolved_variances'] ?? 0), 'tone' => 'maroon'],
            ],
            'charts' => [
                'branch_sales_trend' => $sales['charts']['sales_trend'] ?? collect(),
                'branch_expense_trend' => $financial['charts']['expense_trend'] ?? collect(),
                'branch_airtime_trend' => $airtime['charts']['commission_trend'] ?? collect(),
                'branch_inventory_movement' => $inventory['charts']['inventory_movement_trend'] ?? collect(),
            ],
            'tables' => [
                'recent_branch_sales' => $sales['tables']['recent_sales'] ?? collect(),
                'recent_branch_expenses' => $financial['tables']['recent_expenses'] ?? collect(),
                'pending_branch_approvals' => $financial['tables']['pending_daily_closings'] ?? collect(),
                'branch_low_stock' => $inventory['tables']['low_stock_products'] ?? collect(),
            ],
            'kpi_alerts' => $alerts,
        ];
    }
}
