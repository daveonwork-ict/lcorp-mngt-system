<?php

use App\Http\Controllers\HeldTransactionController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\SalePaymentController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SalesDashboardController;
use App\Http\Controllers\SaleVoidController;
use Illuminate\Support\Facades\Route;

Route::get('/pos', [POSController::class, 'index'])
    ->middleware('permission:view_pos')
    ->name('pos.index');

Route::post('/pos/checkout', [POSController::class, 'checkout'])
    ->middleware('permission:create_sale')
    ->name('pos.checkout');

Route::middleware('permission:view_sales')->group(function (): void {
    Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');
    Route::get('/sales/{sale}', [SalesController::class, 'show'])->whereNumber('sale')->name('sales.show');
    Route::get('/sales/{sale}/receipt', [ReceiptController::class, 'show'])->whereNumber('sale')->name('sales.receipt');
});

Route::post('/sales/{sale}/payments', [SalePaymentController::class, 'store'])
    ->whereNumber('sale')
    ->middleware('permission:create_sale')
    ->name('sales.payments.store');

Route::get('/sales/{sale}/receipt/reprint', [ReceiptController::class, 'reprint'])
    ->whereNumber('sale')
    ->middleware('permission:reprint_receipt')
    ->name('sales.receipt.reprint');

Route::middleware('permission:manage_payment_methods')->group(function (): void {
    Route::get('/sales/payment-methods', [PaymentMethodController::class, 'index'])->name('sales.payment-methods.index');
    Route::post('/sales/payment-methods', [PaymentMethodController::class, 'store'])->name('sales.payment-methods.store');
    Route::put('/sales/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'update'])->name('sales.payment-methods.update');
});

Route::middleware('permission:hold_transaction')->group(function (): void {
    Route::get('/sales/held-transactions', [HeldTransactionController::class, 'index'])->name('sales.held.index');
    Route::post('/sales/held-transactions', [HeldTransactionController::class, 'store'])->name('sales.held.store');
    Route::post('/sales/held-transactions/{heldTransaction}/resume', [HeldTransactionController::class, 'resume'])->name('sales.held.resume');
    Route::post('/sales/held-transactions/{heldTransaction}/cancel', [HeldTransactionController::class, 'cancel'])->name('sales.held.cancel');
});

Route::middleware('permission:void_sale')->group(function (): void {
    Route::get('/sales/void-requests', [SaleVoidController::class, 'index'])->name('sales.voids.index');
    Route::post('/sales/{sale}/void-requests', [SaleVoidController::class, 'store'])->whereNumber('sale')->name('sales.voids.store');
});

Route::middleware('permission:approve_void_sale')->group(function (): void {
    Route::post('/sales/void-requests/{voidRequest}/approve', [SaleVoidController::class, 'approve'])->name('sales.voids.approve');
    Route::post('/sales/void-requests/{voidRequest}/reject', [SaleVoidController::class, 'reject'])->name('sales.voids.reject');
});

Route::middleware('permission:create_sales_return')->group(function (): void {
    Route::get('/sales/returns', [SaleReturnController::class, 'index'])->name('sales.returns.index');
    Route::post('/sales/returns', [SaleReturnController::class, 'store'])->name('sales.returns.store');
});

Route::middleware('permission:approve_sales_return')->group(function (): void {
    Route::post('/sales/returns/{saleReturn}/approve', [SaleReturnController::class, 'approve'])->name('sales.returns.approve');
    Route::post('/sales/returns/{saleReturn}/reject', [SaleReturnController::class, 'reject'])->name('sales.returns.reject');
});

Route::get('/sales-dashboard', [SalesDashboardController::class, 'index'])
    ->middleware('permission:view_sales_dashboard')
    ->name('sales.dashboard');
