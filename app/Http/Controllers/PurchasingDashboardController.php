<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\PurchasingReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchasingDashboardController extends Controller
{
    public function __construct(private readonly PurchasingReportService $reportService)
    {
    }

    public function index(Request $request): View
    {
        return view('purchasing.dashboard.index', [
            'cards' => $this->reportService->dashboardCards($request->integer('branch_id') ?: null),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'selectedBranchId' => $request->integer('branch_id'),
        ]);
    }
}
