<?php

namespace App\Http\Controllers;

use App\Models\Warranty;
use App\Services\WarrantyLookupService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarrantyController extends Controller
{
    public function __construct(private readonly WarrantyLookupService $lookupService)
    {
    }

    public function index(Request $request): View
    {
        return view('warranty.index', [
            'warranties' => $this->lookupService->search($request->only(['warranty_number', 'receipt_number', 'customer', 'imei', 'product', 'branch_id'])),
            'filters' => $request->only(['warranty_number', 'receipt_number', 'customer', 'imei', 'product', 'branch_id']),
        ]);
    }

    public function show(Warranty $warranty): View
    {
        return view('warranty.show', [
            'warranty' => $warranty->load(['sale', 'saleItem.product', 'customer', 'product.brand', 'imei', 'branch', 'claims.statusLogs', 'claims.attachments']),
        ]);
    }
}
