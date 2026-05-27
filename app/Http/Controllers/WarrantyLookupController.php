<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\WarrantyLookupService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarrantyLookupController extends Controller
{
    public function __construct(private readonly WarrantyLookupService $lookupService)
    {
    }

    public function index(Request $request): View
    {
        return view('warranty.lookup', [
            'results' => $this->lookupService->search($request->only(['warranty_number', 'receipt_number', 'customer', 'imei', 'product', 'branch_id'])),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'filters' => $request->only(['warranty_number', 'receipt_number', 'customer', 'imei', 'product', 'branch_id']),
        ]);
    }
}
