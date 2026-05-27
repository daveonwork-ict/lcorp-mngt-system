<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Services\PurchaseRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseRequestController extends Controller
{
    public function __construct(private readonly PurchaseRequestService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('purchasing.purchase-requests.index', [
            'requests' => $this->service->paginate($request->only(['branch_id', 'status'])),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'products' => Product::query()->where('status', 'active')->orderBy('product_name')->get(),
            'filters' => $request->only(['branch_id', 'status']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'request_date' => ['required', 'date'],
            'purpose' => ['required', 'string', 'max:190'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'remarks' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.requested_quantity' => ['required', 'integer', 'min:1'],
            'items.*.estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.remarks' => ['nullable', 'string'],
        ]);

        $this->service->create($validated);

        return back()->with('status', 'Purchase request submitted.');
    }

    public function approve(PurchaseRequest $purchaseRequest): RedirectResponse
    {
        $this->service->approve($purchaseRequest);

        return back()->with('status', 'Purchase request approved.');
    }

    public function reject(Request $request, PurchaseRequest $purchaseRequest): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:3'],
        ]);

        $this->service->reject($purchaseRequest, $validated['rejection_reason']);

        return back()->with('status', 'Purchase request rejected.');
    }
}
