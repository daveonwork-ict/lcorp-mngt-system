<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\AuditLogService;
use App\Services\HrReportService;
use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class HrReportController extends Controller
{
    public function __construct(
        private readonly HrReportService $hrReportService,
        private readonly ReportExportService $reportExportService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorizeView($request);

        $filters = $this->hrReportService->resolveFilters($request->all(), $request->user());

        $this->auditLogService->record('hr_reports', 'report_generated', [], ['filters' => $filters], $filters['branch_id'], 'HR report viewed');

        return view('hr.reports.index', [
            'filters' => $filters,
            'summary' => $this->hrReportService->summary($filters),
            'sections' => $this->hrReportService->sections($filters),
            'branches' => Branch::query()->where('is_active', true)->orderBy('branch_name')->get(),
        ]);
    }

    public function exportCsv(Request $request): Response
    {
        $this->authorizeExport($request);

        $filters = $this->hrReportService->resolveFilters($request->all(), $request->user());
        $rows = $this->hrReportService->exportRows($filters, 1500);

        $csv = $this->reportExportService->toCsv(
            ['Section', 'Reference', 'Employee/Period', 'Branch', 'Date', 'Status', 'Amount', 'Notes'],
            $rows->map(fn (array $row): array => [
                $row['section'],
                $row['reference'],
                $row['employee'],
                $row['branch'],
                $row['date'],
                $row['status'],
                $row['amount'],
                $row['extra'],
            ])
        );

        $this->reportExportService->record('hr', 'csv', $filters, $filters['branch_id'], 'hr-report.csv');

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="hr-report-'.now()->format('Ymd_His').'.csv"',
        ]);
    }

    public function exportExcel(Request $request): Response
    {
        $this->authorizeExport($request);

        $filters = $this->hrReportService->resolveFilters($request->all(), $request->user());
        $rows = $this->hrReportService->exportRows($filters, 1500);

        $content = $this->reportExportService->toExcelTsv(
            ['Section', 'Reference', 'Employee/Period', 'Branch', 'Date', 'Status', 'Amount', 'Notes'],
            $rows->map(fn (array $row): array => [
                $row['section'],
                $row['reference'],
                $row['employee'],
                $row['branch'],
                $row['date'],
                $row['status'],
                $row['amount'],
                $row['extra'],
            ])
        );

        $this->reportExportService->record('hr', 'excel', $filters, $filters['branch_id'], 'hr-report.xls');

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="hr-report-'.now()->format('Ymd_His').'.xls"',
        ]);
    }

    public function printView(Request $request): View
    {
        $this->authorizeView($request);

        $filters = $this->hrReportService->resolveFilters($request->all(), $request->user());

        $this->reportExportService->record('hr', 'print', $filters, $filters['branch_id'], null);

        return view('hr.reports.print', [
            'filters' => $filters,
            'summary' => $this->hrReportService->summary($filters),
            'rows' => $this->hrReportService->exportRows($filters, 1200),
        ]);
    }

    private function authorizeView(Request $request): void
    {
        $user = $request->user();

        if (! $user->hasPermission('view_hr_reports') && $user->role?->code !== config('rms.owner_role_code')) {
            abort(403, 'HR reports access denied.');
        }
    }

    private function authorizeExport(Request $request): void
    {
        $user = $request->user();

        if (! $user->hasPermission('export_hr_reports') && $user->role?->code !== config('rms.owner_role_code')) {
            abort(403, 'HR reports export access denied.');
        }
    }
}
