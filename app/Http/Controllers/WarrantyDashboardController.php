<?php

namespace App\Http\Controllers;

use App\Services\WarrantyAlertService;
use App\Services\WarrantyReportService;
use Illuminate\View\View;

class WarrantyDashboardController extends Controller
{
    public function __construct(
        private readonly WarrantyReportService $reportService,
        private readonly WarrantyAlertService $alertService,
    ) {
    }

    public function index(): View
    {
        $this->alertService->refreshAlerts();

        return view('warranty.dashboard.index', [
            'dashboard' => $this->reportService->dashboard(),
        ]);
    }
}
