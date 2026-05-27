<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\OfficeSupply;
use App\Services\OfficeSupplyInventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfficeSupplyInventoryController extends Controller
{
    public function __construct(private readonly OfficeSupplyInventoryService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('purchasing.office-supplies.inventory', [
            'inventories' => $this->service->paginate($request->only(['branch_id', 'office_supply_id'])),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'supplies' => OfficeSupply::query()->where('status', 'active')->orderBy('supply_name')->get(),
            'filters' => $request->only(['branch_id', 'office_supply_id']),
        ]);
    }

    public function stockIn(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'office_supply_id' => ['required', 'exists:office_supplies,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'remarks' => ['nullable', 'string'],
        ]);

        $this->service->stockIn($validated);

        return back()->with('status', 'Office supply stock-in posted.');
    }
}
