<?php

namespace App\Http\Controllers;

use App\Models\HeldTransaction;
use App\Services\SalesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HeldTransactionController extends Controller
{
    public function __construct(private readonly SalesService $salesService)
    {
    }

    public function index(): View
    {
        return view('sales.held-transactions', [
            'heldTransactions' => HeldTransaction::query()
                ->with(['branch', 'cashier'])
                ->latest('id')
                ->paginate(20),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'customer_id' => ['nullable', 'integer'],
            'remarks' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.imei_id' => ['nullable', 'exists:product_imeis,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.selling_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $held = $this->salesService->hold($validated);

        return redirect()->route('sales.held.index')->with('status', 'Transaction held: '.$held->hold_number);
    }

    public function resume(HeldTransaction $heldTransaction): RedirectResponse
    {
        if ((int) $heldTransaction->cashier_id !== (int) auth()->id()) {
            abort(403, 'Held transaction belongs to another cashier.');
        }

        if ($heldTransaction->status !== 'held') {
            abort(422, 'Held transaction is not active.');
        }

        $heldTransaction->update(['status' => 'resumed']);

        return redirect()->route('pos.index', ['held' => $heldTransaction->id])->with('status', 'Held transaction resumed.');
    }

    public function cancel(HeldTransaction $heldTransaction): RedirectResponse
    {
        if ((int) $heldTransaction->cashier_id !== (int) auth()->id()) {
            abort(403, 'Held transaction belongs to another cashier.');
        }

        $heldTransaction->update(['status' => 'cancelled']);

        return back()->with('status', 'Held transaction cancelled.');
    }
}
