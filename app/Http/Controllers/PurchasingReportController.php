<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Supplier;
use App\Services\PurchasingReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PurchasingReportController extends Controller
{
    public function __construct(private readonly PurchasingReportService $service)
    {
    }

    public function index(Request $request): View
    {
        $filters = $request->only(['date_from', 'date_to', 'branch_id', 'supplier_id']);

        return view('purchasing.reports.index', [
            'purchaseRequests' => $this->service->purchaseRequestReport($filters),
            'payables' => $this->service->payableAgingReport($filters),
            'issuances' => $this->service->officeSupplyUsageReport($filters),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'suppliers' => Supplier::query()->orderBy('supplier_name')->get(),
            'filters' => $filters,
        ]);
    }

    public function exportCsv(Request $request): Response
    {
        $rows = $this->service->payableAgingReport($request->only(['branch_id', 'supplier_id']))->items();

        $csv = implode(',', ['Payable Number', 'Supplier', 'Branch', 'Due Date', 'Total', 'Paid', 'Balance', 'Status'])."\n";
        foreach ($rows as $row) {
            $csv .= implode(',', [
                $row->payable_number,
                str_replace(',', ' ', (string) ($row->supplier?->supplier_name ?? '')),
                str_replace(',', ' ', (string) ($row->branch?->name ?? '')),
                $row->due_date,
                $row->total_amount,
                $row->amount_paid,
                $row->balance_amount,
                $row->payment_status,
            ])."\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="purchasing-payables.csv"',
        ]);
    }
}
