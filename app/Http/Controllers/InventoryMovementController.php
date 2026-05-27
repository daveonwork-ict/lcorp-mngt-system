<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryMovementController extends Controller
{
    public function index(Request $request): View
    {
        $movements = InventoryMovement::query()
            ->with(['branch', 'product'])
            ->when($request->integer('branch_id'), fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->when($request->integer('product_id'), fn ($q, $productId) => $q->where('product_id', $productId))
            ->when($request->filled('movement_type'), fn ($q) => $q->where('movement_type', $request->string('movement_type')))
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('inventory.movements.index', [
            'movements' => $movements,
            'branches' => \App\Models\Branch::query()->orderBy('name')->get(),
            'products' => \App\Models\Product::query()->orderBy('product_name')->get(),
            'filters' => $request->only(['branch_id', 'product_id', 'movement_type']),
        ]);
    }
}
