<?php

namespace App\Http\Controllers;

use App\Models\AirtimeProvider;
use App\Models\AirtimeTransaction;
use App\Models\AirtimeWallet;
use App\Models\Branch;
use App\Models\PaymentMethod;
use App\Services\AirtimeTransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AirtimeTransactionController extends Controller
{
    public function __construct(private readonly AirtimeTransactionService $transactionService)
    {
    }

    public function index(Request $request): View
    {
        return view('airtime.transactions.index', [
            'transactions' => $this->transactionService->paginate($request->only(['date_from', 'date_to', 'branch_id', 'provider_id', 'status'])),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'providers' => AirtimeProvider::query()->where('status', 'active')->orderBy('provider_name')->get(),
            'wallets' => AirtimeWallet::query()->where('status', 'active')->orderBy('wallet_number')->get(),
            'paymentMethods' => PaymentMethod::query()->where('status', 'active')->orderBy('payment_method_name')->get(),
            'filters' => $request->only(['date_from', 'date_to', 'branch_id', 'provider_id', 'status']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'provider_id' => ['required', 'exists:airtime_providers,id'],
            'wallet_id' => ['required', 'exists:airtime_wallets,id'],
            'customer_mobile_number' => ['required', 'string', 'max:20'],
            'load_amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
            'payment_reference' => ['nullable', 'string', 'max:190'],
            'remarks' => ['nullable', 'string'],
            'transaction_status' => ['required', 'in:successful,pending,failed,cancelled,reversed'],
            'commission_override' => ['nullable', 'array'],
            'commission_override.commission_type' => ['nullable', 'in:fixed,percentage,none'],
            'commission_override.commission_value' => ['nullable', 'numeric', 'min:0'],
        ]);

        $transaction = $this->transactionService->create($validated);

        return redirect()->route('airtime.transactions.show', $transaction)->with('status', 'Airtime transaction recorded.');
    }

    public function show(AirtimeTransaction $transaction): View
    {
        return view('airtime.transactions.show', [
            'transaction' => $transaction->load(['branch', 'provider', 'wallet', 'cashier', 'paymentMethod']),
        ]);
    }

    public function reverse(Request $request, AirtimeTransaction $transaction): RedirectResponse
    {
        $validated = $request->validate([
            'reversal_reason' => ['required', 'string', 'min:3'],
        ]);

        $this->transactionService->reverse($transaction, $validated['reversal_reason']);

        return back()->with('status', 'Airtime transaction reversed.');
    }
}
