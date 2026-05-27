<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function __construct(private readonly SupplierService $supplierService)
    {
    }

    public function index(Request $request): View
    {
        return view('purchasing.suppliers.index', [
            'suppliers' => $this->supplierService->paginate($request->only(['status', 'search'])),
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_name' => ['required', 'string', 'max:180'],
            'contact_person' => ['nullable', 'string', 'max:180'],
            'contact_number' => ['nullable', 'string', 'max:60'],
            'email' => ['nullable', 'email', 'max:180'],
            'address' => ['nullable', 'string'],
            'product_categories' => ['nullable', 'array'],
            'payment_terms' => ['nullable', 'string', 'max:180'],
            'status' => ['required', 'in:active,inactive,blacklisted'],
            'remarks' => ['nullable', 'string'],
        ]);

        $this->supplierService->create($validated);

        return back()->with('status', 'Supplier created successfully.');
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_name' => ['required', 'string', 'max:180'],
            'contact_person' => ['nullable', 'string', 'max:180'],
            'contact_number' => ['nullable', 'string', 'max:60'],
            'email' => ['nullable', 'email', 'max:180'],
            'address' => ['nullable', 'string'],
            'product_categories' => ['nullable', 'array'],
            'payment_terms' => ['nullable', 'string', 'max:180'],
            'status' => ['required', 'in:active,inactive,blacklisted'],
            'remarks' => ['nullable', 'string'],
        ]);

        $this->supplierService->update($supplier, $validated);

        return back()->with('status', 'Supplier updated successfully.');
    }

    public function show(Supplier $supplier): View
    {
        return view('purchasing.suppliers.show', [
            'supplier' => $supplier,
            'profile' => $this->supplierService->profile($supplier),
            'recentOrders' => $supplier->purchaseOrders()->latest('id')->limit(10)->get(),
            'openPayables' => $supplier->payables()->whereIn('payment_status', ['unpaid', 'partial'])->latest('id')->limit(10)->get(),
        ]);
    }
}
