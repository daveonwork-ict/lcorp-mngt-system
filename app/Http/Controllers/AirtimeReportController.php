<?php

namespace App\Http\Controllers;

use App\Models\AirtimeProvider;
use App\Models\Branch;
use App\Models\User;
use App\Services\AirtimeReportService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AirtimeReportController extends Controller
{
    public function __construct(
        private readonly AirtimeReportService $reportService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function index(Request $request): View
    {
        $filters = $request->only(['date_from', 'date_to', 'branch_id', 'provider_id', 'cashier_id', 'status']);

        return view('airtime.reports.index', [
            'transactions' => $this->reportService->transactions($filters),
            'walletBalances' => $this->reportService->walletBalances($filters),
            'fundings' => $this->reportService->fundings($filters),
            'ledgers' => $this->reportService->ledgers($filters),
            'commissions' => $this->reportService->commissions($filters),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'providers' => AirtimeProvider::query()->where('status', 'active')->orderBy('provider_name')->get(),
            'cashiers' => User::query()->orderBy('full_name')->get(),
            'filters' => $filters,
        ]);
    }

    public function exportCsv(Request $request): Response
    {
        $rows = $this->reportService->transactions($request->only(['date_from', 'date_to', 'branch_id', 'provider_id', 'cashier_id', 'status']))->items();

        $content = implode(',', ['Transaction No', 'Branch', 'Provider', 'Mobile', 'Load Amount', 'Commission', 'Status', 'Processed At'])."\n";
        foreach ($rows as $row) {
            $content .= implode(',', [
                $row->transaction_number,
                str_replace(',', ' ', (string) ($row->branch?->name ?? '')),
                str_replace(',', ' ', (string) ($row->provider?->provider_name ?? '')),
                $row->customer_mobile_number,
                $row->load_amount,
                $row->commission_amount,
                $row->transaction_status,
                $row->processed_at,
            ])."\n";
        }

        $this->auditLogService->record('airtime', 'report_exported', [], ['type' => 'csv'], $request->integer('branch_id'), 'Airtime report exported (csv)');

        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="airtime-report.csv"',
        ]);
    }

    public function printView(Request $request): View
    {
        $filters = $request->only(['date_from', 'date_to', 'branch_id', 'provider_id', 'cashier_id', 'status']);

        $this->auditLogService->record('airtime', 'report_exported', [], ['type' => 'print'], $request->integer('branch_id'), 'Airtime report print view generated');

        return view('airtime.reports.print', [
            'transactions' => $this->reportService->transactions($filters),
            'filters' => $filters,
        ]);
    }
}
