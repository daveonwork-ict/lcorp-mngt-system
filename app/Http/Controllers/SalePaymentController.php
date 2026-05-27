<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Services\PaymentService;
use App\Services\SalesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SalePaymentController extends Controller
{
    public function __construct(
        private readonly SalesService $salesService,
        private readonly PaymentService $paymentService,
    ) {
    }

    public function store(Request $request, Sale $sale): RedirectResponse
    {
        $this->salesService->ensureCanAccessSale($sale);

        $validated = $request->validate([
            'payments' => ['required', 'array', 'min:1'],
            'payments.*.payment_method_id' => ['required', 'exists:payment_methods,id'],
            'payments.*.payment_reference' => ['nullable', 'string', 'max:190'],
            'payments.*.amount' => ['required', 'numeric', 'min:0.01'],
            'payments.*.remarks' => ['nullable', 'string'],
        ]);

        $result = $this->paymentService->validate((float) $sale->total_amount, $validated['payments'], true);
        $this->paymentService->record($sale, $result['payments']);

        $sale->update([
            'paid_amount' => (float) $sale->paid_amount + $result['paid_amount'],
            'change_amount' => $result['change_amount'],
            'payment_status' => $result['payment_status'],
        ]);

        return back()->with('status', 'Payment recorded successfully.');
    }
}
