<?php

namespace App\Http\Controllers;

use App\Models\AirtimeWallet;
use App\Models\AirtimeWalletAdjustment;
use App\Services\AirtimeWalletAdjustmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AirtimeWalletAdjustmentController extends Controller
{
    public function __construct(private readonly AirtimeWalletAdjustmentService $adjustmentService)
    {
    }

    public function index(Request $request): View
    {
        return view('airtime.adjustments.index', [
            'adjustments' => $this->adjustmentService->paginate($request->only(['status', 'branch_id'])),
            'wallets' => AirtimeWallet::query()->with(['branch', 'provider'])->where('status', 'active')->orderBy('wallet_number')->get(),
            'filters' => $request->only(['status', 'branch_id']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'wallet_id' => ['required', 'exists:airtime_wallets,id'],
            'adjustment_type' => ['required', 'in:increase,decrease'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'min:3'],
            'remarks' => ['nullable', 'string'],
        ]);

        $this->adjustmentService->request($validated);

        return back()->with('status', 'Wallet adjustment request submitted.');
    }

    public function approve(Request $request, AirtimeWalletAdjustment $adjustment): RedirectResponse
    {
        $validated = $request->validate([
            'approval_remarks' => ['nullable', 'string'],
        ]);

        $this->adjustmentService->approve($adjustment, $validated['approval_remarks'] ?? null);

        return back()->with('status', 'Wallet adjustment approved.');
    }

    public function reject(Request $request, AirtimeWalletAdjustment $adjustment): RedirectResponse
    {
        $validated = $request->validate([
            'approval_remarks' => ['nullable', 'string'],
        ]);

        $this->adjustmentService->reject($adjustment, $validated['approval_remarks'] ?? null);

        return back()->with('status', 'Wallet adjustment rejected.');
    }
}
