<?php

namespace App\Http\Controllers;

use App\Models\OfficeSupplyCategory;
use App\Services\OfficeSupplyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfficeSupplyCategoryController extends Controller
{
    public function __construct(private readonly OfficeSupplyService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('purchasing.office-supplies.categories', [
            'categories' => $this->service->categoryList($request->only(['status'])),
            'filters' => $request->only(['status']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->service->createCategory($validated);

        return back()->with('status', 'Office supply category created.');
    }

    public function update(Request $request, OfficeSupplyCategory $officeSupplyCategory): RedirectResponse
    {
        $validated = $request->validate([
            'category_name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->service->updateCategory($officeSupplyCategory, $validated);

        return back()->with('status', 'Office supply category updated.');
    }
}
