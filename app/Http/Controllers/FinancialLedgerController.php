<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\FinancialReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinancialLedgerController extends Controller
{
    public function __construct(private readonly FinancialReportService $reportService)
    {
    }

    public function index(Request $request): View
    {
        return view('finance.ledger.index', [
            'ledgers' => $this->reportService->ledgers($request->only(['branch_id', 'date_from', 'date_to'])),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'filters' => $request->only(['branch_id', 'date_from', 'date_to']),
        ]);
    }
}
