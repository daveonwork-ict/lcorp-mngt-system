<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\FundTransfer;
use App\Services\FundTransferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FundTransferController extends Controller
{
    public function __construct(private readonly FundTransferService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('finance.transfers.index', [
            'transfers' => $this->service->paginate($request->only(['status', 'branch_id'])),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'filters' => $request->only(['status', 'branch_id']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'source_branch_id' => ['nullable', 'exists:branches,id'],
            'destination_branch_id' => ['nullable', 'exists:branches,id', 'different:source_branch_id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transfer_method' => ['required', 'string', 'max:120'],
            'reference_number' => ['nullable', 'string', 'max:190'],
            'proof_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'remarks' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('proof_file')) {
            $validated['proof_file'] = $request->file('proof_file')->store('finance/fund-transfer-proofs');
        }

        $this->service->request($validated);

        return back()->with('status', 'Fund transfer requested.');
    }

    public function approve(FundTransfer $transfer): RedirectResponse
    {
        $this->service->approve($transfer);

        return back()->with('status', 'Fund transfer approved.');
    }

    public function reject(Request $request, FundTransfer $transfer): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:3'],
        ]);

        $this->service->reject($transfer, $validated['rejection_reason']);

        return back()->with('status', 'Fund transfer rejected.');
    }
}
