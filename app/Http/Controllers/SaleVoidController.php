<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleVoidRequest;
use App\Services\SaleVoidService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleVoidController extends Controller
{
    public function __construct(private readonly SaleVoidService $saleVoidService)
    {
    }

    public function index(): View
    {
        return view('sales.void-requests', [
            'voidRequests' => SaleVoidRequest::query()->with(['sale.branch', 'requester', 'approver'])->latest('id')->paginate(20),
        ]);
    }

    public function store(Request $request, Sale $sale): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:3'],
        ]);

        $this->saleVoidService->request($sale, $validated['reason']);

        return back()->with('status', 'Void request submitted for approval.');
    }

    public function approve(Request $request, SaleVoidRequest $voidRequest): RedirectResponse
    {
        $validated = $request->validate([
            'approval_remarks' => ['nullable', 'string'],
        ]);

        $this->saleVoidService->approve($voidRequest, $validated['approval_remarks'] ?? '');

        return back()->with('status', 'Void request approved.');
    }

    public function reject(Request $request, SaleVoidRequest $voidRequest): RedirectResponse
    {
        $validated = $request->validate([
            'approval_remarks' => ['nullable', 'string'],
        ]);

        $this->saleVoidService->reject($voidRequest, $validated['approval_remarks'] ?? '');

        return back()->with('status', 'Void request rejected.');
    }
}
