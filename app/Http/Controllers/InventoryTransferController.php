<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransfer;
use App\Services\InventoryTransferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryTransferController extends Controller
{
    public function __construct(private readonly InventoryTransferService $transferService)
    {
    }

    public function index(): View
    {
        return view('inventory.transfers.index', [
            'transfers' => InventoryTransfer::query()->with(['sourceBranch', 'destinationBranch'])->latest('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('inventory.transfers.form', [
            'branches' => \App\Models\Branch::query()->where('is_active', true)->get(),
            'products' => \App\Models\Product::query()->where('status', 'active')->get(),
            'imeis' => \App\Models\ProductImei::query()->where('status', 'available')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'transfer_number' => ['required', 'string', 'max:120', 'unique:inventory_transfers,transfer_number'],
            'source_branch_id' => ['required', 'exists:branches,id'],
            'destination_branch_id' => ['required', 'exists:branches,id', 'different:source_branch_id'],
            'remarks' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,pending_approval,approved,in_transit,received,rejected,cancelled'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.imei_id' => ['nullable', 'exists:product_imeis,id'],
            'items.*.remarks' => ['nullable', 'string'],
        ]);

        $transfer = $this->transferService->create($validated);

        return redirect()->route('inventory.transfers.show', $transfer)->with('status', 'Transfer request created.');
    }

    public function show(InventoryTransfer $transfer): View
    {
        return view('inventory.transfers.show', [
            'transfer' => $transfer->load(['sourceBranch', 'destinationBranch', 'items.product', 'items.productImei']),
        ]);
    }

    public function approve(InventoryTransfer $transfer): RedirectResponse
    {
        $this->transferService->approve($transfer->load('items.productImei'));

        return back()->with('status', 'Transfer approved and marked in transit.');
    }

    public function receive(InventoryTransfer $transfer): RedirectResponse
    {
        $this->transferService->receive($transfer->load('items.productImei'));

        return back()->with('status', 'Transfer received and inventory updated.');
    }
}
