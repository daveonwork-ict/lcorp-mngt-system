<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\PaymentMethod;
use App\Services\CashInService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashInController extends Controller
{
    public function __construct(private readonly CashInService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('finance.cash-ins.index', [
            'cashIns' => $this->service->paginate($request->only(['branch_id', 'source_type'])),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'paymentMethods' => PaymentMethod::query()->where('status', 'active')->orderBy('payment_method_name')->get(),
            'filters' => $request->only(['branch_id', 'source_type']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'source_type' => ['required', 'string', 'max:120'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
            'remarks' => ['nullable', 'string'],
        ]);

        $this->service->record($validated + ['received_at' => now()]);

        return back()->with('status', 'Cash-in recorded.');
    }
}
