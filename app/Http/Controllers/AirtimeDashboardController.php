<?php

namespace App\Http\Controllers;

use App\Services\AirtimeDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AirtimeDashboardController extends Controller
{
    public function __construct(private readonly AirtimeDashboardService $dashboardService)
    {
    }

    public function index(Request $request): View
    {
        $summary = $this->dashboardService->summary($request->integer('branch_id'));

        return view('airtime.dashboard.index', [
            'cards' => $summary['cards'],
            'charts' => $summary['charts'],
            'tables' => $summary['tables'],
            'branches' => \App\Models\Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'selectedBranchId' => $request->integer('branch_id'),
        ]);
    }
}
