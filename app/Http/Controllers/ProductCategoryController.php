<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductCategoryController extends Controller
{
    public function __construct(private readonly ProductService $productService)
    {
    }

    public function index(): View
    {
        return view('inventory.categories.index', [
            'categories' => $this->productService->categories(),
        ]);
    }

    public function create(): View
    {
        return view('inventory.categories.form', ['category' => new ProductCategory(), 'mode' => 'create']);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_code' => ['required', 'string', 'max:80', 'unique:product_categories,category_code'],
            'category_name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->productService->createCategory($validated);

        return redirect()->route('inventory.categories.index')->with('status', 'Category created.');
    }

    public function edit(ProductCategory $category): View
    {
        return view('inventory.categories.form', ['category' => $category, 'mode' => 'edit']);
    }

    public function update(Request $request, ProductCategory $category): RedirectResponse
    {
        $validated = $request->validate([
            'category_code' => ['required', 'string', 'max:80', 'unique:product_categories,category_code,'.$category->id],
            'category_name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->productService->updateCategory($category, $validated);

        return redirect()->route('inventory.categories.index')->with('status', 'Category updated.');
    }

    public function destroy(ProductCategory $category): RedirectResponse
    {
        $this->productService->updateCategory($category, ['status' => 'inactive']);

        return back()->with('status', 'Category deactivated.');
    }
}
