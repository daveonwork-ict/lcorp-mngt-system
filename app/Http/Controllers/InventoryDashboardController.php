<?php

namespace App\Http\Controllers;

use App\Services\InventoryDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryDashboardController extends Controller
{
    public function __construct(private readonly InventoryDashboardService $dashboardService)
    {
    }

    public function index(Request $request): View
    {
        $summary = $this->dashboardService->summary($request->integer('branch_id'));

        return view('inventory.dashboard.index', [
            'cards' => $summary['cards'],
            'charts' => $summary['charts'],
            'branches' => \App\Models\Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'selectedBranchId' => $request->integer('branch_id'),
        ]);
    }
}
