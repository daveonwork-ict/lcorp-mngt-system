<?php

namespace App\Http\Controllers;

use App\Models\PhysicalCount;
use App\Services\PhysicalCountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhysicalCountController extends Controller
{
    public function __construct(private readonly PhysicalCountService $physicalCountService)
    {
    }

    public function index(): View
    {
        return view('inventory.physical-counts.index', [
            'counts' => PhysicalCount::query()->with('branch')->latest('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('inventory.physical-counts.form', [
            'branches' => \App\Models\Branch::query()->where('is_active', true)->get(),
            'categories' => \App\Models\ProductCategory::query()->where('status', 'active')->get(),
            'products' => \App\Models\Product::query()->where('status', 'active')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'count_number' => ['required', 'string', 'max:120', 'unique:physical_counts,count_number'],
            'branch_id' => ['required', 'exists:branches,id'],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'status' => ['required', 'in:open,submitted,reviewed,adjusted,cancelled'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.counted_quantity' => ['required', 'integer', 'min:0'],
            'items.*.encoded_imei' => ['nullable', 'string', 'max:190'],
            'items.*.remarks' => ['nullable', 'string'],
        ]);

        $count = $this->physicalCountService->create($validated);

        return redirect()->route('inventory.physical-counts.show', $count)->with('status', 'Physical count created.');
    }

    public function show(PhysicalCount $physicalCount): View
    {
        return view('inventory.physical-counts.show', [
            'count' => $physicalCount->load(['branch', 'items.product']),
        ]);
    }

    public function submit(PhysicalCount $physicalCount): RedirectResponse
    {
        $this->physicalCountService->submit($physicalCount->load('items'));

        return back()->with('status', 'Physical count submitted for review.');
    }

    public function generateAdjustment(PhysicalCount $physicalCount): RedirectResponse
    {
        $adjustment = $this->physicalCountService->createAdjustmentFromVariance($physicalCount->load('items'));

        if (! $adjustment) {
            return back()->with('status', 'No variance found.');
        }

        return redirect()->route('inventory.adjustments.show', $adjustment)->with('status', 'Adjustment generated from variance.');
    }
}
