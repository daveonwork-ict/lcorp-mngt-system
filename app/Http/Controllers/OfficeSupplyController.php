<?php

namespace App\Http\Controllers;

use App\Models\OfficeSupply;
use App\Models\OfficeSupplyCategory;
use App\Services\OfficeSupplyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfficeSupplyController extends Controller
{
    public function __construct(private readonly OfficeSupplyService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('purchasing.office-supplies.index', [
            'supplies' => $this->service->supplyList($request->only(['category_id', 'status'])),
            'categories' => OfficeSupplyCategory::query()->orderBy('category_name')->get(),
            'filters' => $request->only(['category_id', 'status']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supply_name' => ['required', 'string', 'max:180'],
            'category_id' => ['required', 'exists:office_supply_categories,id'],
            'unit' => ['required', 'string', 'max:60'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->service->createSupply($validated);

        return back()->with('status', 'Office supply created.');
    }

    public function update(Request $request, OfficeSupply $officeSupply): RedirectResponse
    {
        $validated = $request->validate([
            'supply_name' => ['required', 'string', 'max:180'],
            'category_id' => ['required', 'exists:office_supply_categories,id'],
            'unit' => ['required', 'string', 'max:60'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->service->updateSupply($officeSupply, $validated);

        return back()->with('status', 'Office supply updated.');
    }
}
