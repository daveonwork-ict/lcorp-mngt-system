<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\ExpenseCategory;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\FinancialReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class FinancialReportController extends Controller
{
    public function __construct(
        private readonly FinancialReportService $reportService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function index(Request $request): View
    {
        $filters = $request->only(['date_from', 'date_to', 'branch_id', 'category_id', 'user_id', 'status']);

        return view('finance.reports.index', [
            'reports' => $this->reportService->data($filters),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'categories' => ExpenseCategory::query()->orderBy('category_name')->get(),
            'users' => User::query()->orderBy('full_name')->get(),
            'filters' => $filters,
        ]);
    }

    public function exportCsv(Request $request): Response
    {
        $rows = $this->reportService->expenses($request->only(['date_from', 'date_to', 'branch_id', 'category_id', 'user_id', 'status']))->items();

        $csv = implode(',', ['Expense #', 'Date', 'Branch', 'Category', 'Payee', 'Amount', 'Status'])."\n";
        foreach ($rows as $row) {
            $csv .= implode(',', [
                $row->expense_number,
                optional($row->expense_date)->format('Y-m-d'),
                $row->branch?->name,
                $row->category?->category_name,
                '"'.str_replace('"', '""', $row->vendor_or_payee).'"',
                number_format((float) $row->amount, 2, '.', ''),
                $row->status,
            ])."\n";
        }

        $this->auditLogService->record('finance', 'financial_report_exported', [], ['type' => 'csv'], null, 'Financial report exported (CSV)');

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="financial-expenses-'.now()->format('Ymd_His').'.csv"',
        ]);
    }

    public function exportExcel(Request $request): Response
    {
        $rows = $this->reportService->expenses($request->only(['date_from', 'date_to', 'branch_id', 'category_id', 'user_id', 'status']))->items();

        $content = "Expense #\tDate\tBranch\tCategory\tPayee\tAmount\tStatus\n";
        foreach ($rows as $row) {
            $content .= implode("\t", [
                $row->expense_number,
                optional($row->expense_date)->format('Y-m-d'),
                $row->branch?->name,
                $row->category?->category_name,
                $row->vendor_or_payee,
                number_format((float) $row->amount, 2, '.', ''),
                $row->status,
            ])."\n";
        }

        $this->auditLogService->record('finance', 'financial_report_exported', [], ['type' => 'excel'], null, 'Financial report exported (Excel)');

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="financial-expenses-'.now()->format('Ymd_His').'.xls"',
        ]);
    }

    public function exportPdf(Request $request): View
    {
        $filters = $request->only(['date_from', 'date_to', 'branch_id', 'category_id', 'user_id', 'status']);
        $this->auditLogService->record('finance', 'financial_report_exported', [], ['type' => 'pdf_print'], null, 'Financial report exported (PDF print view)');

        return view('finance.reports.print', [
            'reports' => $this->reportService->data($filters),
            'filters' => $filters,
        ]);
    }

    public function printView(Request $request): View
    {
        $filters = $request->only(['date_from', 'date_to', 'branch_id', 'category_id', 'user_id', 'status']);

        return view('finance.reports.print', [
            'reports' => $this->reportService->data($filters),
            'filters' => $filters,
        ]);
    }
}
