<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Supplier;
use App\Services\SupplierPayableService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierPayableController extends Controller
{
    public function __construct(private readonly SupplierPayableService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('purchasing.payables.index', [
            'payables' => $this->service->paginate($request->only(['supplier_id', 'branch_id', 'payment_status'])),
            'suppliers' => Supplier::query()->orderBy('supplier_name')->get(),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'aging' => $this->service->agingSummary($request->integer('supplier_id') ?: null),
            'filters' => $request->only(['supplier_id', 'branch_id', 'payment_status']),
        ]);
    }
}
