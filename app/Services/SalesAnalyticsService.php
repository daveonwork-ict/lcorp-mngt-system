<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\SaleReturn;

class SalesAnalyticsService
{
    public function summary(array $filters = []): array
    {
        $query = Sale::query()->where('sales_status', 'completed');

        $query->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId));
        $query->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('sales_date', '>=', $date));
        $query->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('sales_date', '<=', $date));

        $todaySales = (float) (clone $query)->whereDate('sales_date', today())->sum('total_amount');
        $monthSales = (float) (clone $query)->whereMonth('sales_date', now()->month)->whereYear('sales_date', now()->year)->sum('total_amount');
        $totalSales = (float) (clone $query)->sum('total_amount');
        $transactions = (int) (clone $query)->count();
        $avgTransaction = $transactions > 0 ? $totalSales / $transactions : 0;

        return [
            'cards' => [
                'today_sales' => $todaySales,
                'monthly_sales' => $monthSales,
                'total_sales' => $totalSales,
                'transactions' => $transactions,
                'average_transaction' => $avgTransaction,
                'return_count' => SaleReturn::query()->when($filters['branch_id'] ?? null, fn ($q, $branchId) => $q->where('branch_id', $branchId))->count(),
            ],
            'charts' => [
                'sales_trend' => (clone $query)
                    ->selectRaw('sales_date as label, SUM(total_amount) as value')
                    ->groupBy('sales_date')
                    ->orderBy('sales_date')
                    ->limit(31)
                    ->get(),
                'sales_per_branch' => (clone $query)
                    ->selectRaw('branch_id as label, SUM(total_amount) as value')
                    ->groupBy('branch_id')
                    ->get(),
                'sales_by_cashier' => (clone $query)
                    ->selectRaw('cashier_id as label, SUM(total_amount) as value')
                    ->groupBy('cashier_id')
                    ->orderByDesc('value')
                    ->limit(10)
                    ->get(),
                'sales_by_payment_method' => SalePayment::query()
                    ->selectRaw('payment_method_id as label, SUM(amount) as value')
                    ->groupBy('payment_method_id')
                    ->get(),
                'top_selling_products' => SaleItem::query()
                    ->selectRaw('product_id as label, SUM(quantity) as value')
                    ->groupBy('product_id')
                    ->orderByDesc('value')
                    ->limit(10)
                    ->get(),
            ],
            'tables' => [
                'recent_sales' => (clone $query)->with(['branch', 'cashier'])->latest('id')->limit(10)->get(),
            ],
        ];
    }
}
