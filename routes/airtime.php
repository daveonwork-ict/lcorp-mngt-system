<?php

use App\Http\Controllers\AirtimeCommissionController;
use App\Http\Controllers\AirtimeDashboardController;
use App\Http\Controllers\AirtimeFundingController;
use App\Http\Controllers\AirtimeProviderController;
use App\Http\Controllers\AirtimeReportController;
use App\Http\Controllers\AirtimeTransactionController;
use App\Http\Controllers\AirtimeWalletAdjustmentController;
use App\Http\Controllers\AirtimeWalletController;
use App\Http\Controllers\AirtimeWalletLedgerController;
use Illuminate\Support\Facades\Route;

Route::get('/airtime', [AirtimeDashboardController::class, 'index'])
    ->middleware('permission:view_airtime_dashboard')
    ->name('airtime.index');

Route::get('/airtime/dashboard', [AirtimeDashboardController::class, 'index'])
    ->middleware('permission:view_airtime_dashboard')
    ->name('airtime.dashboard');

Route::middleware('permission:manage_airtime_providers')->group(function (): void {
    Route::get('/airtime/providers', [AirtimeProviderController::class, 'index'])->name('airtime.providers.index');
    Route::post('/airtime/providers', [AirtimeProviderController::class, 'store'])->name('airtime.providers.store');
    Route::put('/airtime/providers/{provider}', [AirtimeProviderController::class, 'update'])->name('airtime.providers.update');
});

Route::middleware('permission:view_airtime_wallets')->group(function (): void {
    Route::get('/airtime/wallets', [AirtimeWalletController::class, 'index'])->name('airtime.wallets.index');
    Route::get('/airtime/wallets/{wallet}', [AirtimeWalletController::class, 'show'])->name('airtime.wallets.show');
    Route::get('/airtime/ledgers', [AirtimeWalletLedgerController::class, 'index'])->name('airtime.ledgers.index');
});

Route::post('/airtime/wallets', [AirtimeWalletController::class, 'store'])
    ->middleware('permission:create_wallet_adjustment')
    ->name('airtime.wallets.store');

Route::put('/airtime/wallets/{wallet}', [AirtimeWalletController::class, 'update'])
    ->middleware('permission:create_wallet_adjustment')
    ->name('airtime.wallets.update');

Route::middleware('permission:create_wallet_funding')->group(function (): void {
    Route::get('/airtime/fundings', [AirtimeFundingController::class, 'index'])->name('airtime.fundings.index');
    Route::post('/airtime/fundings', [AirtimeFundingController::class, 'store'])->name('airtime.fundings.store');
});

Route::middleware('permission:approve_wallet_funding')->group(function (): void {
    Route::post('/airtime/fundings/{funding}/approve', [AirtimeFundingController::class, 'approve'])->name('airtime.fundings.approve');
    Route::post('/airtime/fundings/{funding}/reject', [AirtimeFundingController::class, 'reject'])->name('airtime.fundings.reject');
});

Route::middleware('permission:view_airtime_transactions')->group(function (): void {
    Route::get('/airtime/transactions', [AirtimeTransactionController::class, 'index'])->name('airtime.transactions.index');
    Route::get('/airtime/transactions/{transaction}', [AirtimeTransactionController::class, 'show'])->name('airtime.transactions.show');
    Route::get('/airtime/commissions', [AirtimeCommissionController::class, 'index'])->name('airtime.commissions.index');
});

Route::post('/airtime/transactions', [AirtimeTransactionController::class, 'store'])
    ->middleware('permission:create_airtime_transaction')
    ->name('airtime.transactions.store');

Route::post('/airtime/transactions/{transaction}/reverse', [AirtimeTransactionController::class, 'reverse'])
    ->middleware('permission:reverse_airtime_transaction')
    ->name('airtime.transactions.reverse');

Route::middleware('permission:create_wallet_adjustment')->group(function (): void {
    Route::get('/airtime/adjustments', [AirtimeWalletAdjustmentController::class, 'index'])->name('airtime.adjustments.index');
    Route::post('/airtime/adjustments', [AirtimeWalletAdjustmentController::class, 'store'])->name('airtime.adjustments.store');
});

Route::middleware('permission:approve_wallet_adjustment')->group(function (): void {
    Route::post('/airtime/adjustments/{adjustment}/approve', [AirtimeWalletAdjustmentController::class, 'approve'])->name('airtime.adjustments.approve');
    Route::post('/airtime/adjustments/{adjustment}/reject', [AirtimeWalletAdjustmentController::class, 'reject'])->name('airtime.adjustments.reject');
});

Route::get('/airtime/reports', [AirtimeReportController::class, 'index'])
    ->middleware('permission:view_airtime_reports')
    ->name('airtime.reports.index');

Route::get('/airtime/reports/export-csv', [AirtimeReportController::class, 'exportCsv'])
    ->middleware('permission:export_airtime_reports')
    ->name('airtime.reports.export-csv');

Route::get('/airtime/reports/print', [AirtimeReportController::class, 'printView'])
    ->middleware('permission:view_airtime_reports')
    ->name('airtime.reports.print');
