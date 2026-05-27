<?php

use App\Http\Controllers\CashDenominationController;
use App\Http\Controllers\CashFlowDashboardController;
use App\Http\Controllers\CashInController;
use App\Http\Controllers\CashOpeningController;
use App\Http\Controllers\CashOutController;
use App\Http\Controllers\CashVarianceController;
use App\Http\Controllers\DailyClosingController;
use App\Http\Controllers\ExpenseApprovalController;
use App\Http\Controllers\ExpenseAttachmentController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FinancialLedgerController;
use App\Http\Controllers\FinancialReportController;
use App\Http\Controllers\FundTransferController;
use Illuminate\Support\Facades\Route;

Route::get('/cash-flow', [CashFlowDashboardController::class, 'index'])
    ->middleware('permission:view_cash_flow')
    ->name('cash-flow.index');

Route::middleware('permission:create_cash_opening')->group(function (): void {
    Route::get('/cash-flow/openings', [CashOpeningController::class, 'index'])->name('finance.openings.index');
    Route::post('/cash-flow/openings', [CashOpeningController::class, 'store'])->name('finance.openings.store');
    Route::post('/cash-flow/openings/{opening}/close', [CashOpeningController::class, 'close'])->name('finance.openings.close');
});

Route::middleware('permission:create_cash_in')->group(function (): void {
    Route::get('/cash-flow/cash-ins', [CashInController::class, 'index'])->name('finance.cash-ins.index');
    Route::post('/cash-flow/cash-ins', [CashInController::class, 'store'])->name('finance.cash-ins.store');
});

Route::middleware('permission:create_cash_out')->group(function (): void {
    Route::get('/cash-flow/cash-outs', [CashOutController::class, 'index'])->name('finance.cash-outs.index');
    Route::post('/cash-flow/cash-outs', [CashOutController::class, 'store'])->name('finance.cash-outs.store');
});

Route::middleware('permission:view_expenses')->group(function (): void {
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('/expenses/attachments/{attachment}/download', [ExpenseAttachmentController::class, 'download'])->name('expenses.attachments.download');
    Route::get('/expenses/{expense}', [ExpenseController::class, 'show'])->name('expenses.show');
});

Route::post('/expenses', [ExpenseController::class, 'store'])
    ->middleware('permission:create_expense')
    ->name('expenses.store');

Route::middleware('permission:approve_expense')->group(function (): void {
    Route::post('/expenses/{expense}/approve', [ExpenseApprovalController::class, 'approve'])->name('expenses.approve');
    Route::post('/expenses/{expense}/reject', [ExpenseApprovalController::class, 'reject'])->name('expenses.reject');
});

Route::middleware('permission:view_expenses')->group(function (): void {
    Route::get('/expenses/categories', [ExpenseCategoryController::class, 'index'])->name('expenses.categories.index');
});

Route::post('/expenses/categories', [ExpenseCategoryController::class, 'store'])
    ->middleware('permission:create_expense')
    ->name('expenses.categories.store');

Route::put('/expenses/categories/{category}', [ExpenseCategoryController::class, 'update'])
    ->middleware('permission:create_expense')
    ->name('expenses.categories.update');

Route::middleware('permission:view_daily_closing')->group(function (): void {
    Route::get('/cash-flow/daily-closing', [DailyClosingController::class, 'index'])->name('finance.closings.index');
});

Route::post('/cash-flow/daily-closing', [DailyClosingController::class, 'store'])
    ->middleware('permission:submit_daily_closing')
    ->name('finance.closings.store');

Route::post('/cash-flow/daily-closing/{closing}/review', [DailyClosingController::class, 'review'])
    ->middleware('permission:review_daily_closing')
    ->name('finance.closings.review');

Route::post('/cash-flow/daily-closing/{closing}/denominations', [CashDenominationController::class, 'store'])
    ->middleware('permission:submit_daily_closing')
    ->name('finance.closings.denominations.store');

Route::get('/cash-flow/variances', [CashVarianceController::class, 'index'])
    ->middleware('permission:view_daily_closing')
    ->name('finance.variances.index');

Route::post('/cash-flow/variances/{variance}/resolve', [CashVarianceController::class, 'resolve'])
    ->middleware('permission:review_daily_closing')
    ->name('finance.variances.resolve');

Route::middleware('permission:manage_fund_transfers')->group(function (): void {
    Route::get('/cash-flow/transfers', [FundTransferController::class, 'index'])->name('finance.transfers.index');
    Route::post('/cash-flow/transfers', [FundTransferController::class, 'store'])->name('finance.transfers.store');
    Route::post('/cash-flow/transfers/{transfer}/approve', [FundTransferController::class, 'approve'])->name('finance.transfers.approve');
    Route::post('/cash-flow/transfers/{transfer}/reject', [FundTransferController::class, 'reject'])->name('finance.transfers.reject');
});

Route::get('/cash-flow/ledger', [FinancialLedgerController::class, 'index'])
    ->middleware('permission:view_financial_ledger')
    ->name('finance.ledger.index');

Route::get('/cash-flow/reports', [FinancialReportController::class, 'index'])
    ->middleware('permission:view_financial_reports')
    ->name('finance.reports.index');

Route::get('/cash-flow/reports/export-csv', [FinancialReportController::class, 'exportCsv'])
    ->middleware('permission:export_financial_reports')
    ->name('finance.reports.export-csv');

Route::get('/cash-flow/reports/export-excel', [FinancialReportController::class, 'exportExcel'])
    ->middleware('permission:export_financial_reports')
    ->name('finance.reports.export-excel');

Route::get('/cash-flow/reports/export-pdf', [FinancialReportController::class, 'exportPdf'])
    ->middleware('permission:export_financial_reports')
    ->name('finance.reports.export-pdf');

Route::get('/cash-flow/reports/print', [FinancialReportController::class, 'printView'])
    ->middleware('permission:view_financial_reports')
    ->name('finance.reports.print');
