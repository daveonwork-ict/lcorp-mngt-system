<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Services\PurchaseOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function __construct(private readonly PurchaseOrderService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('purchasing.purchase-orders.index', [
            'orders' => $this->service->paginate($request->only(['branch_id', 'supplier_id', 'status'])),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'suppliers' => Supplier::query()->where('status', 'active')->orderBy('supplier_name')->get(),
            'requests' => PurchaseRequest::query()->where('status', 'approved')->latest('id')->limit(100)->get(),
            'products' => Product::query()->where('status', 'active')->orderBy('product_name')->get(),
            'filters' => $request->only(['branch_id', 'supplier_id', 'status']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'request_id' => ['nullable', 'exists:purchase_requests,id'],
            'po_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'items.*.product_id' => ['required_with:items', 'exists:products,id'],
            'items.*.quantity_ordered' => ['required_with:items', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.remarks' => ['nullable', 'string'],
        ]);

        $this->service->create($validated);

        return back()->with('status', 'Purchase order created.');
    }

    public function approve(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->service->approve($purchaseOrder);

        return back()->with('status', 'Purchase order approved.');
    }

    public function send(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->service->markSent($purchaseOrder);

        return back()->with('status', 'Purchase order marked as sent.');
    }
}
