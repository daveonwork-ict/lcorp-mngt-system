<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductPriceHistory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductPriceHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $histories = ProductPriceHistory::query()
            ->with('product')
            ->when($request->integer('product_id'), fn ($q, $productId) => $q->where('product_id', $productId))
            ->latest('changed_at')
            ->paginate(20)
            ->withQueryString();

        return view('inventory.price-histories.index', [
            'histories' => $histories,
            'products' => Product::query()->orderBy('product_name')->get(),
            'filters' => $request->only('product_id'),
        ]);
    }
}
