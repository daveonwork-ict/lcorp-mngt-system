<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\PaymentMethod;
use App\Models\Sale;
use App\Models\User;
use App\Services\SalesService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesController extends Controller
{
    public function __construct(private readonly SalesService $salesService)
    {
    }

    public function index(Request $request): View
    {
        return view('sales.index', [
            'sales' => $this->salesService->list($request->only(['date_from', 'date_to', 'branch_id', 'cashier_id', 'payment_status', 'sales_status'])),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'cashiers' => User::query()->orderBy('full_name')->get(),
            'paymentMethods' => PaymentMethod::query()->where('status', 'active')->orderBy('payment_method_name')->get(),
            'filters' => $request->only(['date_from', 'date_to', 'branch_id', 'cashier_id', 'payment_status', 'sales_status']),
        ]);
    }

    public function show(Sale $sale): View
    {
        $this->salesService->ensureCanAccessSale($sale);

        return view('sales.show', [
            'sale' => $sale->load(['branch', 'cashier', 'items.product', 'items.imei', 'payments.paymentMethod', 'voidRequests', 'returns']),
        ]);
    }
}
