<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Branch;
use App\Models\Product;
use App\Services\AuditLogService;
use App\Services\WarrantyReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class WarrantyReportController extends Controller
{
    public function __construct(
        private readonly WarrantyReportService $reportService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function index(Request $request): View
    {
        $filters = $request->only(['date_from', 'date_to', 'branch_id', 'status', 'product_id', 'brand_id']);

        return view('warranty.reports.index', [
            'warranties' => $this->reportService->warranties($filters),
            'claims' => $this->reportService->claims($filters),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'products' => Product::query()->orderBy('product_name')->get(),
            'brands' => Brand::query()->orderBy('brand_name')->get(),
            'filters' => $filters,
        ]);
    }

    public function exportCsv(Request $request): Response
    {
        $rows = $this->reportService->warranties($request->only(['date_from', 'date_to', 'branch_id', 'status', 'product_id', 'brand_id']))->items();

        $csv = implode(',', ['Warranty #', 'Customer', 'Product', 'Start', 'End', 'Status'])."\n";
        foreach ($rows as $row) {
            $csv .= implode(',', [
                $row->warranty_number,
                '"'.str_replace('"', '""', (string) $row->customer?->full_name).'"',
                '"'.str_replace('"', '""', (string) $row->product?->product_name).'"',
                optional($row->warranty_start_date)->format('Y-m-d'),
                optional($row->warranty_end_date)->format('Y-m-d'),
                $row->warranty_status,
            ])."\n";
        }

        $this->auditLogService->record('warranty', 'warranty_report_exported', [], ['type' => 'csv'], null, 'Warranty report exported');

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="warranty-report-'.now()->format('Ymd_His').'.csv"',
        ]);
    }

    public function exportExcel(Request $request): Response
    {
        $rows = $this->reportService->warranties($request->only(['date_from', 'date_to', 'branch_id', 'status', 'product_id', 'brand_id']))->items();

        $content = "Warranty #\tCustomer\tProduct\tStart\tEnd\tStatus\n";
        foreach ($rows as $row) {
            $content .= implode("\t", [
                $row->warranty_number,
                $row->customer?->full_name,
                $row->product?->product_name,
                optional($row->warranty_start_date)->format('Y-m-d'),
                optional($row->warranty_end_date)->format('Y-m-d'),
                $row->warranty_status,
            ])."\n";
        }

        $this->auditLogService->record('warranty', 'warranty_report_exported', [], ['type' => 'excel'], null, 'Warranty report exported');

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="warranty-report-'.now()->format('Ymd_His').'.xls"',
        ]);
    }

    public function printView(Request $request): View
    {
        $filters = $request->only(['date_from', 'date_to', 'branch_id', 'status', 'product_id', 'brand_id']);

        return view('warranty.reports.print', [
            'warranties' => $this->reportService->warranties($filters),
            'claims' => $this->reportService->claims($filters),
            'filters' => $filters,
        ]);
    }
}
