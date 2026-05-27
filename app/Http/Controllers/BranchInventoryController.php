<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Branch;
use App\Models\ProductCategory;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchInventoryController extends Controller
{
    public function __construct(private readonly InventoryService $inventoryService)
    {
    }

    public function index(Request $request): View
    {
        if ($request->integer('branch_id')) {
            $this->inventoryService->validateBranchAccess($request->integer('branch_id'));
        }

        return view('inventory.branch-inventory.index', [
            'inventories' => $this->inventoryService->branchInventory($request->only(['branch_id', 'category_id', 'brand_id', 'search'])),
            'branches' => Branch::query()->orderBy('name')->get(),
            'categories' => ProductCategory::query()->orderBy('category_name')->get(),
            'brands' => Brand::query()->orderBy('brand_name')->get(),
            'filters' => $request->only(['branch_id', 'category_id', 'brand_id', 'search']),
        ]);
    }
}
