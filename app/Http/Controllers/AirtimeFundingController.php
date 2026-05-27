<?php

namespace App\Http\Controllers;

use App\Models\AirtimeWallet;
use App\Models\AirtimeWalletFunding;
use App\Models\Branch;
use App\Services\AirtimeFundingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AirtimeFundingController extends Controller
{
    public function __construct(private readonly AirtimeFundingService $fundingService)
    {
    }

    public function index(Request $request): View
    {
        return view('airtime.fundings.index', [
            'fundings' => $this->fundingService->paginate($request->only(['status', 'branch_id'])),
            'wallets' => AirtimeWallet::query()->with(['branch', 'provider'])->where('status', 'active')->orderBy('wallet_number')->get(),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'filters' => $request->only(['status', 'branch_id']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'wallet_id' => ['required', 'exists:airtime_wallets,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'funding_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:120'],
            'reference_number' => ['nullable', 'string', 'max:190'],
            'proof_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'remarks' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('proof_file')) {
            $validated['proof_file'] = $request->file('proof_file')->store('airtime/funding-proofs', 'public');
        }

        $this->fundingService->request($validated);

        return back()->with('status', 'Wallet funding request submitted.');
    }

    public function approve(AirtimeWalletFunding $funding): RedirectResponse
    {
        $this->fundingService->approve($funding);

        return back()->with('status', 'Wallet funding approved.');
    }

    public function reject(Request $request, AirtimeWalletFunding $funding): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:3'],
        ]);

        $this->fundingService->reject($funding, $validated['rejection_reason']);

        return back()->with('status', 'Wallet funding rejected.');
    }
}
