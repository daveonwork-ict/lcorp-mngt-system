<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService)
    {
    }

    public function index(Request $request): View
    {
        return view('inventory.products.index', [
            'products' => $this->productService->products($request->only(['search', 'category_id', 'brand_id', 'status'])),
            'categories' => ProductCategory::query()->orderBy('category_name')->get(),
            'brands' => Brand::query()->orderBy('brand_name')->get(),
            'filters' => $request->only(['search', 'category_id', 'brand_id', 'status']),
        ]);
    }

    public function create(): View
    {
        return view('inventory.products.form', [
            'product' => new Product(),
            'categories' => ProductCategory::query()->where('status', 'active')->get(),
            'brands' => Brand::query()->where('status', 'active')->get(),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_code' => ['required', 'string', 'max:100', 'unique:products,product_code'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
            'barcode' => ['nullable', 'string', 'max:120', 'unique:products,barcode'],
            'product_name' => ['required', 'string', 'max:190'],
            'category_id' => ['required', 'exists:product_categories,id'],
            'brand_id' => ['required', 'exists:brands,id'],
            'model' => ['nullable', 'string', 'max:120'],
            'variant' => ['nullable', 'string', 'max:120'],
            'color' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'warranty_duration' => ['required', 'integer', 'min:0'],
            'warranty_duration_type' => ['required', 'in:day,week,month,year'],
            'is_serialized' => ['nullable', 'boolean'],
            'is_imei_required' => ['nullable', 'boolean'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $validated['is_serialized'] = $request->boolean('is_serialized');
        $validated['is_imei_required'] = $request->boolean('is_imei_required');

        $product = $this->productService->createProduct($validated);

        return redirect()->route('inventory.products.show', $product)->with('status', 'Product created.');
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'brand', 'imeis', 'inventories.branch']);

        return view('inventory.products.show', ['product' => $product]);
    }

    public function edit(Product $product): View
    {
        return view('inventory.products.form', [
            'product' => $product,
            'categories' => ProductCategory::query()->where('status', 'active')->get(),
            'brands' => Brand::query()->where('status', 'active')->get(),
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'product_code' => ['required', 'string', 'max:100', 'unique:products,product_code,'.$product->id],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku,'.$product->id],
            'barcode' => ['nullable', 'string', 'max:120', 'unique:products,barcode,'.$product->id],
            'product_name' => ['required', 'string', 'max:190'],
            'category_id' => ['required', 'exists:product_categories,id'],
            'brand_id' => ['required', 'exists:brands,id'],
            'model' => ['nullable', 'string', 'max:120'],
            'variant' => ['nullable', 'string', 'max:120'],
            'color' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'warranty_duration' => ['required', 'integer', 'min:0'],
            'warranty_duration_type' => ['required', 'in:day,week,month,year'],
            'is_serialized' => ['nullable', 'boolean'],
            'is_imei_required' => ['nullable', 'boolean'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $validated['is_serialized'] = $request->boolean('is_serialized');
        $validated['is_imei_required'] = $request->boolean('is_imei_required');

        $this->productService->updateProduct($product, $validated);

        return redirect()->route('inventory.products.show', $product)->with('status', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->productService->updateProduct($product, ['status' => 'inactive'] + $product->toArray());

        return back()->with('status', 'Product deactivated.');
    }
}
