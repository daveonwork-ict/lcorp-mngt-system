<?php

namespace App\Http\Controllers;

use App\Models\InventoryAdjustment;
use App\Services\InventoryAdjustmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryAdjustmentController extends Controller
{
    public function __construct(private readonly InventoryAdjustmentService $adjustmentService)
    {
    }

    public function index(): View
    {
        return view('inventory.adjustments.index', [
            'adjustments' => InventoryAdjustment::query()->with('branch')->latest('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('inventory.adjustments.form', [
            'branches' => \App\Models\Branch::query()->where('is_active', true)->get(),
            'products' => \App\Models\Product::query()->where('status', 'active')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'adjustment_number' => ['required', 'string', 'max:120', 'unique:inventory_adjustments,adjustment_number'],
            'branch_id' => ['required', 'exists:branches,id'],
            'reason' => ['required', 'string'],
            'remarks' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,pending,approved,rejected,cancelled'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity_after' => ['required', 'integer', 'min:0'],
            'items.*.remarks' => ['nullable', 'string'],
        ]);

        $adjustment = $this->adjustmentService->create($validated);

        return redirect()->route('inventory.adjustments.show', $adjustment)->with('status', 'Adjustment request saved.');
    }

    public function show(InventoryAdjustment $adjustment): View
    {
        return view('inventory.adjustments.show', [
            'adjustment' => $adjustment->load(['branch', 'items.product']),
        ]);
    }

    public function approve(InventoryAdjustment $adjustment): RedirectResponse
    {
        $this->adjustmentService->approve($adjustment->load('items'));

        return back()->with('status', 'Adjustment approved.');
    }
}
