<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\BranchInventoryController;
use App\Http\Controllers\InventoryAdjustmentController;
use App\Http\Controllers\InventoryAlertController;
use App\Http\Controllers\InventoryDashboardController;
use App\Http\Controllers\InventoryMovementController;
use App\Http\Controllers\InventoryTransferController;
use App\Http\Controllers\OfficeSupplyCategoryController;
use App\Http\Controllers\OfficeSupplyController;
use App\Http\Controllers\OfficeSupplyInventoryController;
use App\Http\Controllers\OfficeSupplyIssuanceController;
use App\Http\Controllers\PhysicalCountController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\PurchasingDashboardController;
use App\Http\Controllers\PurchasingReportController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImeiController;
use App\Http\Controllers\ProductPriceHistoryController;
use App\Http\Controllers\ReceivingReportController;
use App\Http\Controllers\StaffAccountabilityController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierPayableController;
use App\Http\Controllers\SupplierPaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/inventory', [InventoryDashboardController::class, 'index'])
    ->middleware('permission:view_inventory')
    ->name('inventory.index');

Route::middleware('permission:view_inventory')->group(function (): void {
    Route::get('/inventory/dashboard', [InventoryDashboardController::class, 'index'])->name('inventory.dashboard');
    Route::get('/inventory/categories', [ProductCategoryController::class, 'index'])->name('inventory.categories.index');
    Route::get('/inventory/categories/create', [ProductCategoryController::class, 'create'])->name('inventory.categories.create');
    Route::post('/inventory/categories', [ProductCategoryController::class, 'store'])->name('inventory.categories.store');
    Route::get('/inventory/categories/{category}/edit', [ProductCategoryController::class, 'edit'])->name('inventory.categories.edit');
    Route::put('/inventory/categories/{category}', [ProductCategoryController::class, 'update'])->name('inventory.categories.update');
    Route::delete('/inventory/categories/{category}', [ProductCategoryController::class, 'destroy'])->name('inventory.categories.destroy');

    Route::get('/inventory/brands', [BrandController::class, 'index'])->name('inventory.brands.index');
    Route::get('/inventory/brands/create', [BrandController::class, 'create'])->name('inventory.brands.create');
    Route::post('/inventory/brands', [BrandController::class, 'store'])->name('inventory.brands.store');
    Route::get('/inventory/brands/{brand}/edit', [BrandController::class, 'edit'])->name('inventory.brands.edit');
    Route::put('/inventory/brands/{brand}', [BrandController::class, 'update'])->name('inventory.brands.update');
    Route::delete('/inventory/brands/{brand}', [BrandController::class, 'destroy'])->name('inventory.brands.destroy');

    Route::get('/inventory/products', [ProductController::class, 'index'])->name('inventory.products.index');
    Route::get('/inventory/products/{product}', [ProductController::class, 'show'])->whereNumber('product')->name('inventory.products.show');
    Route::get('/inventory/price-histories', [ProductPriceHistoryController::class, 'index'])->name('inventory.price-histories.index');
    Route::get('/inventory/imeis', [ProductImeiController::class, 'index'])->name('inventory.imeis.index');
    Route::post('/inventory/imeis', [ProductImeiController::class, 'store'])->name('inventory.imeis.store');
    Route::post('/inventory/imeis/{imei}/status', [ProductImeiController::class, 'updateStatus'])->name('inventory.imeis.status');
    Route::get('/inventory/branch-inventory', [BranchInventoryController::class, 'index'])->name('inventory.branch-inventory.index');
    Route::get('/inventory/movements', [InventoryMovementController::class, 'index'])->name('inventory.movements.index');
    Route::get('/inventory/alerts', [InventoryAlertController::class, 'index'])->name('inventory.alerts.index');
    Route::post('/inventory/alerts/refresh', [InventoryAlertController::class, 'refresh'])->name('inventory.alerts.refresh');
    Route::post('/inventory/alerts/{alert}/resolve', [InventoryAlertController::class, 'resolve'])->name('inventory.alerts.resolve');
});

Route::middleware('permission:create_product')->group(function (): void {
    Route::get('/inventory/products/create', [ProductController::class, 'create'])->name('inventory.products.create');
    Route::post('/inventory/products', [ProductController::class, 'store'])->name('inventory.products.store');
});

Route::middleware('permission:edit_product')->group(function (): void {
    Route::get('/inventory/products/{product}/edit', [ProductController::class, 'edit'])->name('inventory.products.edit');
    Route::put('/inventory/products/{product}', [ProductController::class, 'update'])->name('inventory.products.update');
});

Route::middleware('permission:deactivate_product')->group(function (): void {
    Route::delete('/inventory/products/{product}', [ProductController::class, 'destroy'])->name('inventory.products.destroy');
});

Route::middleware('permission:view_stock_in')->group(function (): void {
    Route::get('/inventory/stock-ins', [StockInController::class, 'index'])->name('inventory.stock-ins.index');
    Route::get('/inventory/stock-ins/{stockIn}', [StockInController::class, 'show'])->whereNumber('stockIn')->name('inventory.stock-ins.show');
});

Route::middleware('permission:create_stock_in')->group(function (): void {
    Route::get('/inventory/stock-ins/create', [StockInController::class, 'create'])->name('inventory.stock-ins.create');
    Route::post('/inventory/stock-ins', [StockInController::class, 'store'])->name('inventory.stock-ins.store');
});

Route::post('/inventory/stock-ins/{stockIn}/approve', [StockInController::class, 'approve'])
    ->middleware('permission:approve_stock_in')
    ->name('inventory.stock-ins.approve');

Route::middleware('permission:view_stock_adjustment')->group(function (): void {
    Route::get('/inventory/adjustments', [InventoryAdjustmentController::class, 'index'])->name('inventory.adjustments.index');
    Route::get('/inventory/adjustments/{adjustment}', [InventoryAdjustmentController::class, 'show'])->whereNumber('adjustment')->name('inventory.adjustments.show');
});

Route::middleware('permission:create_stock_adjustment')->group(function (): void {
    Route::get('/inventory/adjustments/create', [InventoryAdjustmentController::class, 'create'])->name('inventory.adjustments.create');
    Route::post('/inventory/adjustments', [InventoryAdjustmentController::class, 'store'])->name('inventory.adjustments.store');
});

Route::post('/inventory/adjustments/{adjustment}/approve', [InventoryAdjustmentController::class, 'approve'])
    ->middleware('permission:approve_stock_adjustment')
    ->name('inventory.adjustments.approve');

Route::middleware('permission:view_inventory_transfer')->group(function (): void {
    Route::get('/inventory/transfers', [InventoryTransferController::class, 'index'])->name('inventory.transfers.index');
    Route::get('/inventory/transfers/{transfer}', [InventoryTransferController::class, 'show'])->whereNumber('transfer')->name('inventory.transfers.show');
});

Route::middleware('permission:create_inventory_transfer')->group(function (): void {
    Route::get('/inventory/transfers/create', [InventoryTransferController::class, 'create'])->name('inventory.transfers.create');
    Route::post('/inventory/transfers', [InventoryTransferController::class, 'store'])->name('inventory.transfers.store');
});

Route::post('/inventory/transfers/{transfer}/approve', [InventoryTransferController::class, 'approve'])
    ->middleware('permission:approve_inventory_transfer')
    ->name('inventory.transfers.approve');

Route::post('/inventory/transfers/{transfer}/receive', [InventoryTransferController::class, 'receive'])
    ->middleware('permission:receive_inventory_transfer')
    ->name('inventory.transfers.receive');

Route::middleware('permission:view_physical_count')->group(function (): void {
    Route::get('/inventory/physical-counts', [PhysicalCountController::class, 'index'])->name('inventory.physical-counts.index');
    Route::get('/inventory/physical-counts/{physicalCount}', [PhysicalCountController::class, 'show'])->whereNumber('physicalCount')->name('inventory.physical-counts.show');
});

Route::middleware('permission:create_physical_count')->group(function (): void {
    Route::get('/inventory/physical-counts/create', [PhysicalCountController::class, 'create'])->name('inventory.physical-counts.create');
    Route::post('/inventory/physical-counts', [PhysicalCountController::class, 'store'])->name('inventory.physical-counts.store');
    Route::post('/inventory/physical-counts/{physicalCount}/submit', [PhysicalCountController::class, 'submit'])->name('inventory.physical-counts.submit');
    Route::post('/inventory/physical-counts/{physicalCount}/generate-adjustment', [PhysicalCountController::class, 'generateAdjustment'])->name('inventory.physical-counts.generate-adjustment');
});

Route::middleware('permission:view_suppliers')->group(function (): void {
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
});

Route::middleware('permission:manage_suppliers')->group(function (): void {
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
});

Route::middleware('permission:create_purchase_request')->group(function (): void {
    Route::post('/purchasing/requests', [PurchaseRequestController::class, 'store'])->name('purchasing.requests.store');
});

Route::middleware('permission:approve_purchase_request')->group(function (): void {
    Route::post('/purchasing/requests/{purchaseRequest}/approve', [PurchaseRequestController::class, 'approve'])->name('purchasing.requests.approve');
    Route::post('/purchasing/requests/{purchaseRequest}/reject', [PurchaseRequestController::class, 'reject'])->name('purchasing.requests.reject');
});

Route::middleware('permission:create_purchase_order')->group(function (): void {
    Route::post('/purchasing/orders', [PurchaseOrderController::class, 'store'])->name('purchasing.orders.store');
});

Route::middleware('permission:approve_purchase_order')->group(function (): void {
    Route::post('/purchasing/orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('purchasing.orders.approve');
    Route::post('/purchasing/orders/{purchaseOrder}/send', [PurchaseOrderController::class, 'send'])->name('purchasing.orders.send');
});

Route::middleware('permission:receive_purchase_order')->group(function (): void {
    Route::post('/purchasing/receiving-reports', [ReceivingReportController::class, 'store'])->name('purchasing.receiving-reports.store');
});

Route::middleware('permission:view_purchasing_reports')->group(function (): void {
    Route::get('/purchasing', [PurchasingDashboardController::class, 'index'])->name('purchasing.index');
    Route::get('/purchasing/requests', [PurchaseRequestController::class, 'index'])->name('purchasing.requests.index');
    Route::get('/purchasing/orders', [PurchaseOrderController::class, 'index'])->name('purchasing.orders.index');
    Route::get('/purchasing/receiving-reports', [ReceivingReportController::class, 'index'])->name('purchasing.receiving-reports.index');
    Route::get('/purchasing/receiving-reports/{receivingReport}/download', [ReceivingReportController::class, 'download'])->name('purchasing.receiving-reports.download');
    Route::get('/purchasing/payables', [SupplierPayableController::class, 'index'])->name('purchasing.payables.index');
    Route::get('/purchasing/payments', [SupplierPaymentController::class, 'index'])->name('purchasing.payments.index');
    Route::get('/purchasing/payments/{supplierPayment}/proof', [SupplierPaymentController::class, 'downloadProof'])->name('purchasing.payments.proof');
    Route::get('/purchasing/reports', [PurchasingReportController::class, 'index'])->name('purchasing.reports.index');
});

Route::middleware('permission:record_supplier_payment')->group(function (): void {
    Route::post('/purchasing/payments', [SupplierPaymentController::class, 'store'])->name('purchasing.payments.store');
});

Route::middleware('permission:manage_office_supplies')->group(function (): void {
    Route::get('/office-supplies/categories', [OfficeSupplyCategoryController::class, 'index'])->name('office-supplies.categories.index');
    Route::post('/office-supplies/categories', [OfficeSupplyCategoryController::class, 'store'])->name('office-supplies.categories.store');
    Route::put('/office-supplies/categories/{officeSupplyCategory}', [OfficeSupplyCategoryController::class, 'update'])->name('office-supplies.categories.update');
    Route::get('/office-supplies', [OfficeSupplyController::class, 'index'])->name('office-supplies.index');
    Route::post('/office-supplies', [OfficeSupplyController::class, 'store'])->name('office-supplies.store');
    Route::put('/office-supplies/{officeSupply}', [OfficeSupplyController::class, 'update'])->name('office-supplies.update');
});

Route::middleware('permission:view_office_supply_inventory')->group(function (): void {
    Route::get('/office-supplies/inventory', [OfficeSupplyInventoryController::class, 'index'])->name('office-supplies.inventory.index');
});

Route::middleware('permission:manage_office_supplies')->group(function (): void {
    Route::post('/office-supplies/inventory/stock-in', [OfficeSupplyInventoryController::class, 'stockIn'])->name('office-supplies.inventory.stock-in');
});

Route::middleware('permission:create_supply_issuance')->group(function (): void {
    Route::get('/office-supplies/issuances', [OfficeSupplyIssuanceController::class, 'index'])->name('office-supplies.issuances.index');
    Route::post('/office-supplies/issuances', [OfficeSupplyIssuanceController::class, 'store'])->name('office-supplies.issuances.store');
});

Route::middleware('permission:approve_supply_issuance')->group(function (): void {
    Route::post('/office-supplies/issuances/{officeSupplyIssuance}/approve', [OfficeSupplyIssuanceController::class, 'approve'])->name('office-supplies.issuances.approve');
    Route::post('/office-supplies/issuances/{officeSupplyIssuance}/reject', [OfficeSupplyIssuanceController::class, 'reject'])->name('office-supplies.issuances.reject');
});

Route::middleware('permission:create_supply_issuance')->group(function (): void {
    Route::post('/office-supplies/issuances/{officeSupplyIssuance}/issue', [OfficeSupplyIssuanceController::class, 'issue'])->name('office-supplies.issuances.issue');
});

Route::middleware('permission:view_staff_accountability')->group(function (): void {
    Route::get('/office-supplies/accountabilities', [StaffAccountabilityController::class, 'index'])->name('office-supplies.accountabilities.index');
});

Route::middleware('permission:export_purchasing_reports')->group(function (): void {
    Route::get('/purchasing/reports/export/csv', [PurchasingReportController::class, 'exportCsv'])->name('purchasing.reports.export.csv');
});
