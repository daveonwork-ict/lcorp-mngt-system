<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CashVariance;
use App\Services\CashVarianceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashVarianceController extends Controller
{
    public function __construct(private readonly CashVarianceService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('finance.variances.index', [
            'variances' => $this->service->paginate($request->only(['branch_id', 'resolution_status'])),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'filters' => $request->only(['branch_id', 'resolution_status']),
        ]);
    }

    public function resolve(Request $request, CashVariance $variance): RedirectResponse
    {
        $validated = $request->validate([
            'resolution_status' => ['required', 'in:pending,under_review,resolved,unresolved'],
            'explanation' => ['nullable', 'string'],
        ]);

        $this->service->resolve($variance, $validated['resolution_status'], $validated['explanation'] ?? null);

        return back()->with('status', 'Variance resolution updated.');
    }
}
