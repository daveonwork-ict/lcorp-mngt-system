<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\WarrantyRule;
use App\Services\WarrantyRuleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarrantyRuleController extends Controller
{
    public function __construct(private readonly WarrantyRuleService $service)
    {
    }

    public function index(): View
    {
        return view('warranty.rules.index', [
            'rules' => $this->service->paginate(),
            'categories' => ProductCategory::query()->orderBy('category_name')->get(),
            'brands' => Brand::query()->orderBy('brand_name')->get(),
            'products' => Product::query()->orderBy('product_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rule_name' => ['required', 'string', 'max:190'],
            'product_category_id' => ['nullable', 'exists:product_categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'product_id' => ['nullable', 'exists:products,id'],
            'warranty_duration' => ['required', 'integer', 'min:1'],
            'warranty_duration_type' => ['required', 'in:days,months,years'],
            'warranty_coverage' => ['nullable', 'string'],
            'exclusions' => ['nullable', 'string'],
            'requires_imei' => ['nullable', 'boolean'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->service->create($validated + ['requires_imei' => (bool) ($validated['requires_imei'] ?? false)]);

        return back()->with('status', 'Warranty rule created.');
    }

    public function update(Request $request, WarrantyRule $rule): RedirectResponse
    {
        $validated = $request->validate([
            'rule_name' => ['required', 'string', 'max:190'],
            'product_category_id' => ['nullable', 'exists:product_categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'product_id' => ['nullable', 'exists:products,id'],
            'warranty_duration' => ['required', 'integer', 'min:1'],
            'warranty_duration_type' => ['required', 'in:days,months,years'],
            'warranty_coverage' => ['nullable', 'string'],
            'exclusions' => ['nullable', 'string'],
            'requires_imei' => ['nullable', 'boolean'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->service->update($rule, $validated + ['requires_imei' => (bool) ($validated['requires_imei'] ?? false)]);

        return back()->with('status', 'Warranty rule updated.');
    }
}
