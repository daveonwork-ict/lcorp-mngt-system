<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\InventoryAdjustment;
use App\Models\InventoryMovement;
use App\Models\InventoryTransfer;
use App\Services\ReportExportService;
use App\Services\ReportFilterService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class InventoryReportController extends Controller
{
    public function __construct(
        private readonly ReportFilterService $filterService,
        private readonly ReportExportService $exportService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorizeAccess($request);

        $filters = $this->filterService->normalize($request->all());
        $filters['branch_id'] = $this->filterService->enforceBranchScope($request->user(), $filters['branch_id']);

        $summary = BranchInventory::query()
            ->with(['branch', 'product'])
            ->when($filters['branch_id'], fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->paginate(20)
            ->withQueryString();

        $movements = InventoryMovement::query()
            ->with(['branch', 'product'])
            ->when($filters['branch_id'], fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($filters['date_from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $adjustments = InventoryAdjustment::query()
            ->with(['branch', 'requester'])
            ->when($filters['branch_id'], fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $transfers = InventoryTransfer::query()
            ->with(['sourceBranch', 'destinationBranch'])
            ->when($filters['branch_id'], fn ($q, $branchId) => $q->where('source_branch_id', $branchId)->orWhere('destination_branch_id', $branchId))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('reports.inventory.index', [
            'summary' => $summary,
            'movements' => $movements,
            'adjustments' => $adjustments,
            'transfers' => $transfers,
            'filters' => $filters,
            'branches' => Branch::query()->where('is_active', true)->orderBy('branch_name')->get(),
        ]);
    }

    public function exportCsv(Request $request): Response
    {
        $this->authorizeAccess($request);

        $filters = $this->filterService->normalize($request->all());
        $filters['branch_id'] = $this->filterService->enforceBranchScope($request->user(), $filters['branch_id']);

        $rows = BranchInventory::query()
            ->with(['branch', 'product'])
            ->when($filters['branch_id'], fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->limit(5000)
            ->get();

        $csv = $this->exportService->toCsv(
            ['Branch', 'Product', 'Qty', 'Reorder Level', 'Inventory Value'],
            $rows->map(fn (BranchInventory $row): array => [
                $row->branch?->branch_name ?? $row->branch?->name,
                $row->product?->product_name,
                (string) $row->quantity_available,
                (string) $row->reorder_level,
                number_format((float) $row->inventory_value, 2, '.', ''),
            ])
        );

        $this->exportService->record('inventory', 'csv', $filters, $filters['branch_id'], 'inventory-report.csv');

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="inventory-report-'.now()->format('Ymd_His').'.csv"',
        ]);
    }

    public function exportExcel(Request $request): Response
    {
        $this->authorizeAccess($request);

        $filters = $this->filterService->normalize($request->all());
        $filters['branch_id'] = $this->filterService->enforceBranchScope($request->user(), $filters['branch_id']);

        $rows = BranchInventory::query()
            ->with(['branch', 'product'])
            ->when($filters['branch_id'], fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->limit(5000)
            ->get();

        $content = $this->exportService->toExcelTsv(
            ['Branch', 'Product', 'Qty', 'Reorder Level', 'Inventory Value'],
            $rows->map(fn (BranchInventory $row): array => [
                $row->branch?->branch_name ?? $row->branch?->name,
                $row->product?->product_name,
                (string) $row->quantity_available,
                (string) $row->reorder_level,
                number_format((float) $row->inventory_value, 2, '.', ''),
            ])
        );

        $this->exportService->record('inventory', 'excel', $filters, $filters['branch_id'], 'inventory-report.xls');

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="inventory-report-'.now()->format('Ymd_His').'.xls"',
        ]);
    }

    public function printView(Request $request): View
    {
        $this->authorizeAccess($request);

        $filters = $this->filterService->normalize($request->all());
        $filters['branch_id'] = $this->filterService->enforceBranchScope($request->user(), $filters['branch_id']);

        $summary = BranchInventory::query()
            ->with(['branch', 'product'])
            ->when($filters['branch_id'], fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->limit(1000)
            ->get();

        $this->exportService->record('inventory', 'print', $filters, $filters['branch_id'], null);

        return view('reports.inventory.print', [
            'summary' => $summary,
            'filters' => $filters,
        ]);
    }

    private function authorizeAccess(Request $request): void
    {
        $user = $request->user();
        if (! $user->hasPermission('view_inventory_reports') && ! $user->hasPermission('view_reports') && $user->role?->code !== config('rms.owner_role_code')) {
            abort(403, 'Inventory report access denied.');
        }
    }
}
