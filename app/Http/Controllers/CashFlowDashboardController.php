<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\CashFlowService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashFlowDashboardController extends Controller
{
    public function __construct(private readonly CashFlowService $cashFlowService)
    {
    }

    public function index(Request $request): View
    {
        $branchId = $request->integer('branch_id') ?: null;

        return view('finance.dashboard.index', [
            'dashboard' => $this->cashFlowService->dashboard($branchId),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'filters' => $request->only(['branch_id']),
        ]);
    }
}
