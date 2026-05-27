<?php

namespace App\Http\Controllers;

use App\Models\Warranty;
use App\Services\WarrantyClaimService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarrantyClaimController extends Controller
{
    public function __construct(private readonly WarrantyClaimService $claimService)
    {
    }

    public function index(Request $request): View
    {
        return view('warranty.claims.index', [
            'claims' => $this->claimService->paginate($request->only(['status', 'branch_id'])),
            'filters' => $request->only(['status', 'branch_id']),
            'warranties' => Warranty::query()->with(['customer', 'product'])->whereIn('warranty_status', ['active', 'expired', 'under review', 'claimed'])->latest('id')->limit(100)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'warranty_id' => ['required', 'exists:warranties,id'],
            'claim_date' => ['required', 'date'],
            'issue_description' => ['required', 'string'],
            'product_condition' => ['nullable', 'string'],
        ]);

        $this->claimService->create($validated);

        return back()->with('status', 'Warranty claim submitted.');
    }
}
