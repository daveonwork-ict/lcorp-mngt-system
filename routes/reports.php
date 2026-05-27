<?php

use App\Http\Controllers\AuditReportController;
use App\Http\Controllers\CommunicationReportController;
use App\Http\Controllers\InventoryReportController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\SalesReportController;
use Illuminate\Support\Facades\Route;

Route::get('/reports', [ReportExportController::class, 'index'])
    ->middleware('permission:view_reports')
    ->name('reports.index');

Route::post('/reports/schedules', [ReportExportController::class, 'schedule'])
    ->middleware('permission:manage_dashboard_preferences')
    ->name('reports.schedules.store');

Route::middleware('permission:view_sales_reports')->group(function (): void {
    Route::get('/reports/sales', [SalesReportController::class, 'index'])->name('reports.sales.index');
    Route::get('/reports/sales/export-csv', [SalesReportController::class, 'exportCsv'])->middleware('permission:export_reports')->name('reports.sales.export-csv');
    Route::get('/reports/sales/export-excel', [SalesReportController::class, 'exportExcel'])->middleware('permission:export_reports')->name('reports.sales.export-excel');
    Route::get('/reports/sales/print', [SalesReportController::class, 'printView'])->name('reports.sales.print');
});

Route::middleware('permission:view_inventory_reports')->group(function (): void {
    Route::get('/reports/inventory', [InventoryReportController::class, 'index'])->name('reports.inventory.index');
    Route::get('/reports/inventory/export-csv', [InventoryReportController::class, 'exportCsv'])->middleware('permission:export_reports')->name('reports.inventory.export-csv');
    Route::get('/reports/inventory/export-excel', [InventoryReportController::class, 'exportExcel'])->middleware('permission:export_reports')->name('reports.inventory.export-excel');
    Route::get('/reports/inventory/print', [InventoryReportController::class, 'printView'])->name('reports.inventory.print');
});

Route::get('/reports/communication', [CommunicationReportController::class, 'index'])
    ->middleware('permission:view_communication_reports')
    ->name('reports.communication.index');

Route::get('/reports/audit', [AuditReportController::class, 'index'])
    ->middleware('permission:view_audit_reports')
    ->name('reports.audit.index');

Route::get('/audit-trail', [AuditReportController::class, 'index'])
    ->middleware('permission:view_audit_reports')
    ->name('audit-trail.index');
