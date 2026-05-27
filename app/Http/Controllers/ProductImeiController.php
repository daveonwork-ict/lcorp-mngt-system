<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductImei;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductImeiController extends Controller
{
    public function __construct(private readonly ProductService $productService)
    {
    }

    public function index(Request $request): View
    {
        $imeis = ProductImei::query()
            ->with(['product', 'branch'])
            ->when($request->integer('product_id'), fn ($q, $productId) => $q->where('product_id', $productId))
            ->when($request->integer('branch_id'), fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('inventory.imeis.index', [
            'imeis' => $imeis,
            'products' => Product::query()->orderBy('product_name')->get(),
            'branches' => Branch::query()->orderBy('name')->get(),
            'filters' => $request->only(['product_id', 'branch_id', 'status']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'imei_number' => ['required', 'string', 'max:190', 'unique:product_imeis,imei_number'],
            'serial_number' => ['nullable', 'string', 'max:190'],
            'status' => ['required', 'in:available,sold,reserved,transferred,defective,returned,under_warranty,lost'],
            'received_date' => ['nullable', 'date'],
        ]);

        $this->productService->addImei($validated);

        return back()->with('status', 'IMEI added successfully.');
    }

    public function updateStatus(Request $request, ProductImei $imei): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:available,sold,reserved,transferred,defective,returned,under_warranty,lost'],
        ]);

        if ($imei->status === 'sold' && $validated['status'] === 'sold') {
            abort(422, 'IMEI already sold.');
        }

        $this->productService->updateImeiStatus($imei, $validated['status']);

        return back()->with('status', 'IMEI status updated.');
    }
}
