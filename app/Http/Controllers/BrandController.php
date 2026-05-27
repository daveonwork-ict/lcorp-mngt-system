<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function __construct(private readonly ProductService $productService)
    {
    }

    public function index(): View
    {
        return view('inventory.brands.index', ['brands' => $this->productService->brands()]);
    }

    public function create(): View
    {
        return view('inventory.brands.form', ['brand' => new Brand(), 'mode' => 'create']);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'brand_code' => ['required', 'string', 'max:80', 'unique:brands,brand_code'],
            'brand_name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->productService->createBrand($validated);

        return redirect()->route('inventory.brands.index')->with('status', 'Brand created.');
    }

    public function edit(Brand $brand): View
    {
        return view('inventory.brands.form', ['brand' => $brand, 'mode' => 'edit']);
    }

    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $validated = $request->validate([
            'brand_code' => ['required', 'string', 'max:80', 'unique:brands,brand_code,'.$brand->id],
            'brand_name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->productService->updateBrand($brand, $validated);

        return redirect()->route('inventory.brands.index')->with('status', 'Brand updated.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        $this->productService->updateBrand($brand, ['status' => 'inactive']);

        return back()->with('status', 'Brand deactivated.');
    }
}
