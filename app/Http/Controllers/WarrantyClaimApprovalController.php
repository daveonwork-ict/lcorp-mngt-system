<?php

namespace App\Http\Controllers;

use App\Models\WarrantyClaim;
use App\Services\WarrantyClaimService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WarrantyClaimApprovalController extends Controller
{
    public function __construct(private readonly WarrantyClaimService $claimService)
    {
    }

    public function approve(Request $request, WarrantyClaim $claim): RedirectResponse
    {
        $validated = $request->validate([
            'remarks' => ['nullable', 'string'],
        ]);

        $this->claimService->updateStatus($claim, 'approved', $validated['remarks'] ?? null);

        return back()->with('status', 'Claim approved.');
    }

    public function reject(Request $request, WarrantyClaim $claim): RedirectResponse
    {
        $validated = $request->validate([
            'remarks' => ['required', 'string', 'min:3'],
        ]);

        $this->claimService->updateStatus($claim, 'rejected', $validated['remarks']);

        return back()->with('status', 'Claim rejected.');
    }

    public function updateStatus(Request $request, WarrantyClaim $claim): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,under_review,approved,rejected,under_repair,ready_for_release,released,replaced,cancelled'],
            'remarks' => ['nullable', 'string'],
        ]);

        $this->claimService->updateStatus($claim, $validated['status'], $validated['remarks'] ?? null);

        return back()->with('status', 'Claim status updated.');
    }
}
