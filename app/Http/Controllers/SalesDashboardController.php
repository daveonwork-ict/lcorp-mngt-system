<?php

namespace App\Http\Controllers;

use App\Services\SalesDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesDashboardController extends Controller
{
    public function __construct(private readonly SalesDashboardService $dashboardService)
    {
    }

    public function index(Request $request): View
    {
        $summary = $this->dashboardService->summary($request->integer('branch_id'));

        return view('sales.dashboard', [
            'cards' => $summary['cards'],
            'charts' => $summary['charts'],
            'branches' => \App\Models\Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'selectedBranchId' => $request->integer('branch_id'),
        ]);
    }
}
