<?php

namespace App\Http\Controllers;

use App\Models\AirtimeProvider;
use App\Models\Branch;
use App\Services\AirtimeReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AirtimeCommissionController extends Controller
{
    public function __construct(private readonly AirtimeReportService $reportService)
    {
    }

    public function index(Request $request): View
    {
        return view('airtime.commissions.index', [
            'commissions' => $this->reportService->commissions($request->only(['date_from', 'date_to', 'branch_id', 'provider_id'])),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'providers' => AirtimeProvider::query()->where('status', 'active')->orderBy('provider_name')->get(),
            'filters' => $request->only(['date_from', 'date_to', 'branch_id', 'provider_id']),
        ]);
    }
}
