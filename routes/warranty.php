<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerNoteController;
use App\Http\Controllers\CustomerProfileController;
use App\Http\Controllers\WarrantyClaimApprovalController;
use App\Http\Controllers\WarrantyClaimAttachmentController;
use App\Http\Controllers\WarrantyClaimController;
use App\Http\Controllers\WarrantyController;
use App\Http\Controllers\WarrantyDashboardController;
use App\Http\Controllers\WarrantyLookupController;
use App\Http\Controllers\WarrantyRepairController;
use App\Http\Controllers\WarrantyReplacementController;
use App\Http\Controllers\WarrantyReportController;
use App\Http\Controllers\WarrantyRuleController;
use Illuminate\Support\Facades\Route;

Route::middleware('permission:view_customers')->group(function (): void {
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customer}/profile', [CustomerProfileController::class, 'show'])->name('customers.profile');
});

Route::post('/customers', [CustomerController::class, 'store'])
    ->middleware('permission:create_customer')
    ->name('customers.store');

Route::put('/customers/{customer}', [CustomerController::class, 'update'])
    ->middleware('permission:edit_customer')
    ->name('customers.update');

Route::post('/customers/{customer}/deactivate', [CustomerController::class, 'deactivate'])
    ->middleware('permission:deactivate_customer')
    ->name('customers.deactivate');

Route::post('/customers/{customer}/notes', [CustomerNoteController::class, 'store'])
    ->middleware('permission:view_customer_history')
    ->name('customers.notes.store');

Route::get('/warranty', [WarrantyDashboardController::class, 'index'])
    ->middleware('permission:view_warranties')
    ->name('warranty.index');

Route::get('/warranty/records', [WarrantyController::class, 'index'])
    ->middleware('permission:view_warranties')
    ->name('warranty.records.index');

Route::get('/warranty/records/{warranty}', [WarrantyController::class, 'show'])
    ->middleware('permission:view_warranties')
    ->name('warranty.records.show');

Route::get('/warranty/lookup', [WarrantyLookupController::class, 'index'])
    ->middleware('permission:view_warranties')
    ->name('warranty.lookup.index');

Route::middleware('permission:manage_warranty_rules')->group(function (): void {
    Route::get('/warranty/rules', [WarrantyRuleController::class, 'index'])->name('warranty.rules.index');
    Route::post('/warranty/rules', [WarrantyRuleController::class, 'store'])->name('warranty.rules.store');
    Route::put('/warranty/rules/{rule}', [WarrantyRuleController::class, 'update'])->name('warranty.rules.update');
});

Route::middleware('permission:create_warranty_claim')->group(function (): void {
    Route::get('/warranty/claims', [WarrantyClaimController::class, 'index'])->name('warranty.claims.index');
    Route::post('/warranty/claims', [WarrantyClaimController::class, 'store'])->name('warranty.claims.store');
    Route::post('/warranty/claims/{claim}/attachments', [WarrantyClaimAttachmentController::class, 'store'])->name('warranty.claims.attachments.store');
});

Route::get('/warranty/claims/attachments/{attachment}/download', [WarrantyClaimAttachmentController::class, 'download'])
    ->middleware('permission:view_warranties')
    ->name('warranty.claims.attachments.download');

Route::post('/warranty/claims/{claim}/approve', [WarrantyClaimApprovalController::class, 'approve'])
    ->middleware('permission:approve_warranty_claim')
    ->name('warranty.claims.approve');

Route::post('/warranty/claims/{claim}/reject', [WarrantyClaimApprovalController::class, 'reject'])
    ->middleware('permission:reject_warranty_claim')
    ->name('warranty.claims.reject');

Route::post('/warranty/claims/{claim}/status', [WarrantyClaimApprovalController::class, 'updateStatus'])
    ->middleware('permission:update_warranty_claim_status')
    ->name('warranty.claims.status.update');

Route::post('/warranty/claims/{claim}/repair', [WarrantyRepairController::class, 'store'])
    ->middleware('permission:manage_warranty_repair')
    ->name('warranty.claims.repair.store');

Route::middleware('permission:manage_warranty_replacement')->group(function (): void {
    Route::get('/warranty/replacements', [WarrantyReplacementController::class, 'index'])->name('warranty.replacements.index');
    Route::post('/warranty/claims/{claim}/replacement', [WarrantyReplacementController::class, 'store'])->name('warranty.claims.replacement.store');
});

Route::get('/warranty/reports', [WarrantyReportController::class, 'index'])
    ->middleware('permission:view_warranty_reports')
    ->name('warranty.reports.index');

Route::get('/warranty/reports/export-csv', [WarrantyReportController::class, 'exportCsv'])
    ->middleware('permission:export_warranty_reports')
    ->name('warranty.reports.export-csv');

Route::get('/warranty/reports/export-excel', [WarrantyReportController::class, 'exportExcel'])
    ->middleware('permission:export_warranty_reports')
    ->name('warranty.reports.export-excel');

Route::get('/warranty/reports/print', [WarrantyReportController::class, 'printView'])
    ->middleware('permission:view_warranty_reports')
    ->name('warranty.reports.print');
