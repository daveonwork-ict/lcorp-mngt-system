<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\PaymentMethod;
use App\Models\SupplierPayable;
use App\Models\SupplierPayment;
use App\Services\SupplierPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupplierPaymentController extends Controller
{
    public function __construct(private readonly SupplierPaymentService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('purchasing.payments.index', [
            'payments' => $this->service->paginate($request->only(['supplier_id', 'branch_id'])),
            'payables' => SupplierPayable::query()->with('supplier')->whereIn('payment_status', ['unpaid', 'partial'])->latest('id')->get(),
            'paymentMethods' => PaymentMethod::query()->where('status', 'active')->orderBy('payment_method_name')->get(),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'filters' => $request->only(['supplier_id', 'branch_id']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payable_id' => ['required', 'exists:supplier_payables,id'],
            'payment_date' => ['required', 'date'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
            'reference_number' => ['nullable', 'string', 'max:120'],
            'amount_paid' => ['required', 'numeric', 'min:0.01'],
            'proof_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'remarks' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('proof_file')) {
            $validated['proof_file'] = $request->file('proof_file')->store('purchasing/payment-proofs');
        }

        $this->service->record($validated);

        return back()->with('status', 'Supplier payment recorded.');
    }

    public function downloadProof(SupplierPayment $supplierPayment): StreamedResponse|RedirectResponse
    {
        if (! $supplierPayment->proof_file || ! Storage::exists($supplierPayment->proof_file)) {
            return back()->withErrors(['file' => 'Proof file not found.']);
        }

        return Storage::download($supplierPayment->proof_file, basename($supplierPayment->proof_file));
    }
}
