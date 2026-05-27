<?php

namespace App\Http\Controllers;

use App\Services\POSService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class POSController extends Controller
{
    public function __construct(private readonly POSService $posService)
    {
    }

    public function index(Request $request): View
    {
        $data = $this->posService->posData($request->integer('branch_id'), $request->only(['search', 'category_id']));

        return view('pos.index', $data);
    }

    public function checkout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'customer_id' => ['nullable', 'integer'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string'],
            'allow_partial' => ['nullable', 'boolean'],
            'discount' => ['nullable', 'array'],
            'discount.type' => ['nullable', 'in:fixed,percentage,manual'],
            'discount.value' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.imei_id' => ['nullable', 'exists:product_imeis,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.selling_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'payments' => ['required', 'array', 'min:1'],
            'payments.*.payment_method_id' => ['required', 'exists:payment_methods,id'],
            'payments.*.payment_reference' => ['nullable', 'string', 'max:190'],
            'payments.*.amount' => ['required', 'numeric', 'min:0.01'],
            'payments.*.remarks' => ['nullable', 'string'],
        ]);

        $sale = $this->posService->checkout($validated);

        return redirect()->route('sales.show', $sale)->with('status', 'Sale completed.');
    }
}
