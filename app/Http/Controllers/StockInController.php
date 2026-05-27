<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockIn;
use App\Services\StockInService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockInController extends Controller
{
    public function __construct(private readonly StockInService $stockInService)
    {
    }

    public function index(): View
    {
        return view('inventory.stock-ins.index', [
            'stockIns' => StockIn::query()->with('branch')->latest('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('inventory.stock-ins.form', [
            'products' => Product::query()->where('status', 'active')->orderBy('product_name')->get(),
            'branches' => \App\Models\Branch::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'stock_in_number' => ['required', 'string', 'max:120', 'unique:stock_ins,stock_in_number'],
            'branch_id' => ['required', 'exists:branches,id'],
            'received_date' => ['required', 'date'],
            'reference_number' => ['nullable', 'string', 'max:120'],
            'delivery_receipt_number' => ['nullable', 'string', 'max:120'],
            'remarks' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,pending,approved,rejected,cancelled'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.cost_price' => ['required', 'numeric', 'min:0'],
            'items.*.selling_price' => ['required', 'numeric', 'min:0'],
            'items.*.remarks' => ['nullable', 'string'],
            'items.*.imeis' => ['nullable', 'array'],
            'items.*.imeis.*' => ['string', 'distinct', 'max:190'],
        ]);

        $stockIn = $this->stockInService->create($validated);

        return redirect()->route('inventory.stock-ins.show', $stockIn)->with('status', 'Stock-in saved.');
    }

    public function show(StockIn $stockIn): View
    {
        $stockIn->load(['branch', 'items.product']);

        return view('inventory.stock-ins.show', ['stockIn' => $stockIn]);
    }

    public function approve(StockIn $stockIn): RedirectResponse
    {
        $this->stockInService->approve($stockIn->load('items'));

        return back()->with('status', 'Stock-in approved and posted to inventory.');
    }
}
