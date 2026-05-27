<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleVoidRequest;
use Illuminate\Support\Carbon;

class SalesDashboardService
{
    public function __construct(private readonly BranchAccessService $branchAccessService)
    {
    }

    public function summary(?int $branchId = null): array
    {
        $query = Sale::query()->where('sales_status', 'completed');

        $user = auth()->user();
        if ($user && $user->role?->code !== config('rms.owner_role_code')) {
            $branchIds = $this->branchAccessService->accessibleBranches($user)->pluck('id')->all();
            $query->whereIn('branch_id', $branchIds ?: [-1]);
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        $todaySales = (float) (clone $query)->whereDate('sales_date', $today)->sum('total_amount');
        $monthSales = (float) (clone $query)->whereDate('sales_date', '>=', $monthStart)->sum('total_amount');
        $transactions = (int) (clone $query)->count();
        $averageValue = $transactions > 0 ? round($monthSales / $transactions, 2) : 0;

        $cashSales = (float) (clone $query)->whereHas('payments.paymentMethod', fn ($q) => $q->where('payment_type', 'Cash'))->sum('total_amount');
        $ewalletSales = (float) (clone $query)->whereHas('payments.paymentMethod', fn ($q) => $q->where('payment_type', 'E-Wallet'))->sum('total_amount');
        $pendingVoids = SaleVoidRequest::query()->where('status', 'pending')->count();
        $pendingReturns = SaleReturn::query()->where('status', 'pending')->count();

        return [
            'cards' => [
                ['label' => "Today's Sales", 'value' => number_format($todaySales, 2)],
                ['label' => 'Monthly Sales', 'value' => number_format($monthSales, 2)],
                ['label' => 'Total Transactions', 'value' => $transactions],
                ['label' => 'Average Transaction Value', 'value' => number_format($averageValue, 2)],
                ['label' => 'Cash Sales', 'value' => number_format($cashSales, 2)],
                ['label' => 'E-Wallet Sales', 'value' => number_format($ewalletSales, 2)],
                ['label' => 'Pending Voids', 'value' => $pendingVoids],
                ['label' => 'Pending Returns', 'value' => $pendingReturns],
            ],
            'charts' => [
                'sales_trend' => Sale::query()
                    ->selectRaw('sales_date, SUM(total_amount) as total_sales')
                    ->where('sales_status', 'completed')
                    ->groupBy('sales_date')
                    ->orderBy('sales_date')
                    ->limit(30)
                    ->get(),
                'sales_by_branch' => Sale::query()
                    ->selectRaw('branch_id, SUM(total_amount) as total_sales')
                    ->where('sales_status', 'completed')
                    ->groupBy('branch_id')
                    ->get(),
                'sales_by_cashier' => Sale::query()
                    ->selectRaw('cashier_id, SUM(total_amount) as total_sales')
                    ->where('sales_status', 'completed')
                    ->groupBy('cashier_id')
                    ->get(),
                'sales_by_payment_method' => \App\Models\SalePayment::query()
                    ->selectRaw('payment_method_id, SUM(amount) as total_amount')
                    ->groupBy('payment_method_id')
                    ->get(),
                'top_products' => \App\Models\SaleItem::query()
                    ->selectRaw('product_id, SUM(quantity) as total_quantity')
                    ->groupBy('product_id')
                    ->orderByDesc('total_quantity')
                    ->limit(10)
                    ->get(),
            ],
        ];
    }
}
