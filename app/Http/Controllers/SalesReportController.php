<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\ReportExportService;
use App\Services\ReportFilterService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SalesReportController extends Controller
{
    public function __construct(
        private readonly ReportFilterService $filterService,
        private readonly ReportExportService $exportService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorizeAccess($request);

        $filters = $this->filterService->normalize($request->all());
        $filters['branch_id'] = $this->filterService->enforceBranchScope($request->user(), $filters['branch_id']);

        $sales = Sale::query()
            ->with(['branch', 'cashier', 'customer', 'items.product', 'payments.paymentMethod'])
            ->when($filters['branch_id'], fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['cashier_id'], fn ($q, $cashierId) => $q->where('cashier_id', $cashierId))
            ->when($filters['customer_id'], fn ($q, $customerId) => $q->where('customer_id', $customerId))
            ->when($filters['date_from'], fn ($q, $date) => $q->whereDate('sales_date', '>=', $date))
            ->when($filters['date_to'], fn ($q, $date) => $q->whereDate('sales_date', '<=', $date))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $this->auditLogService->record('reports', 'report_generated', [], ['report_type' => 'sales', 'filters' => $filters], $filters['branch_id'], 'Sales report generated');

        return view('reports.sales.index', [
            'sales' => $sales,
            'filters' => $filters,
            'branches' => Branch::query()->where('is_active', true)->orderBy('branch_name')->get(),
            'cashiers' => User::query()->orderBy('full_name')->get(),
            'products' => Product::query()->orderBy('product_name')->limit(400)->get(),
            'paymentMethods' => PaymentMethod::query()->orderBy('payment_method_name')->get(),
        ]);
    }

    public function exportCsv(Request $request): Response
    {
        $this->authorizeAccess($request);

        $filters = $this->filterService->normalize($request->all());
        $filters['branch_id'] = $this->filterService->enforceBranchScope($request->user(), $filters['branch_id']);

        $rows = Sale::query()
            ->with(['branch', 'cashier', 'customer'])
            ->when($filters['branch_id'], fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['date_from'], fn ($q, $date) => $q->whereDate('sales_date', '>=', $date))
            ->when($filters['date_to'], fn ($q, $date) => $q->whereDate('sales_date', '<=', $date))
            ->latest('id')
            ->limit(5000)
            ->get();

        $csv = $this->exportService->toCsv(
            ['Sale #', 'Date', 'Branch', 'Cashier', 'Customer', 'Total', 'Status'],
            $rows->map(fn (Sale $sale): array => [
                $sale->sales_number,
                optional($sale->sales_date)->format('Y-m-d'),
                $sale->branch?->branch_name ?? $sale->branch?->name,
                $sale->cashier?->display_name,
                $sale->customer?->full_name,
                number_format((float) $sale->total_amount, 2, '.', ''),
                $sale->sales_status,
            ])
        );

        $this->exportService->record('sales', 'csv', $filters, $filters['branch_id'], 'sales-report.csv');

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sales-report-'.now()->format('Ymd_His').'.csv"',
        ]);
    }

    public function printView(Request $request): View
    {
        $this->authorizeAccess($request);

        $filters = $this->filterService->normalize($request->all());
        $filters['branch_id'] = $this->filterService->enforceBranchScope($request->user(), $filters['branch_id']);

        $sales = Sale::query()
            ->with(['branch', 'cashier', 'customer'])
            ->when($filters['branch_id'], fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['date_from'], fn ($q, $date) => $q->whereDate('sales_date', '>=', $date))
            ->when($filters['date_to'], fn ($q, $date) => $q->whereDate('sales_date', '<=', $date))
            ->latest('id')
            ->limit(1000)
            ->get();

        $this->exportService->record('sales', 'print', $filters, $filters['branch_id'], null);

        return view('reports.sales.print', [
            'sales' => $sales,
            'filters' => $filters,
        ]);
    }

    private function authorizeAccess(Request $request): void
    {
        $user = $request->user();
        if (! $user->hasPermission('view_sales_reports') && $user->role?->code !== config('rms.owner_role_code')) {
            abort(403, 'Sales report access denied.');
        }
    }
}
