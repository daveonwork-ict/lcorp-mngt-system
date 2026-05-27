<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImei;
use App\Models\WarrantyClaim;
use App\Services\WarrantyReplacementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarrantyReplacementController extends Controller
{
    public function __construct(private readonly WarrantyReplacementService $replacementService)
    {
    }

    public function index(): View
    {
        return view('warranty.replacements.index', [
            'claims' => WarrantyClaim::query()->with(['warranty.product', 'customer'])->whereIn('claim_status', ['approved', 'under_repair', 'ready_for_release', 'replaced'])->latest('id')->paginate(20),
            'products' => Product::query()->where('status', 'active')->orderBy('product_name')->get(),
            'imeis' => ProductImei::query()->where('status', 'available')->orderBy('imei_number')->get(),
        ]);
    }

    public function store(Request $request, WarrantyClaim $claim): RedirectResponse
    {
        $validated = $request->validate([
            'old_product_id' => ['required', 'exists:products,id'],
            'old_imei_id' => ['nullable', 'exists:product_imeis,id'],
            'replacement_product_id' => ['nullable', 'exists:products,id'],
            'replacement_imei_id' => ['nullable', 'exists:product_imeis,id'],
            'replacement_date' => ['required', 'date'],
            'remarks' => ['nullable', 'string'],
        ]);

        $this->replacementService->replace($claim, $validated);

        return back()->with('status', 'Replacement recorded.');
    }
}
