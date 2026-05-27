<?php

namespace App\Http\Controllers;

use App\Models\AirtimeProvider;
use App\Models\AirtimeWallet;
use App\Models\Branch;
use App\Services\AirtimeWalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AirtimeWalletController extends Controller
{
    public function __construct(private readonly AirtimeWalletService $walletService)
    {
    }

    public function index(Request $request): View
    {
        return view('airtime.wallets.index', [
            'wallets' => $this->walletService->paginate($request->only(['branch_id', 'provider_id', 'status'])),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'providers' => AirtimeProvider::query()->where('status', 'active')->orderBy('provider_name')->get(),
            'filters' => $request->only(['branch_id', 'provider_id', 'status']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'wallet_number' => ['required', 'string', 'max:120', 'unique:airtime_wallets,wallet_number'],
            'branch_id' => ['required', 'exists:branches,id'],
            'provider_id' => ['required', 'exists:airtime_providers,id'],
            'beginning_balance' => ['required', 'numeric', 'min:0'],
            'low_balance_threshold' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->walletService->create($validated);

        return back()->with('status', 'Airtime wallet created.');
    }

    public function show(AirtimeWallet $wallet): View
    {
        return view('airtime.wallets.show', [
            'wallet' => $wallet->load(['branch', 'provider', 'ledgers', 'fundings', 'transactions']),
        ]);
    }

    public function update(Request $request, AirtimeWallet $wallet): RedirectResponse
    {
        $validated = $request->validate([
            'low_balance_threshold' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $this->walletService->update($wallet, $validated);

        return back()->with('status', 'Airtime wallet updated.');
    }
}
