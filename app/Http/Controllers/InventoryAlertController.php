<?php

namespace App\Http\Controllers;

use App\Models\InventoryAlert;
use App\Services\InventoryAlertService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryAlertController extends Controller
{
    public function __construct(private readonly InventoryAlertService $alertService)
    {
    }

    public function index(Request $request): View
    {
        $alerts = InventoryAlert::query()
            ->with(['branch', 'product'])
            ->when($request->filled('alert_type'), fn ($q) => $q->where('alert_type', $request->string('alert_type')))
            ->when($request->filled('severity'), fn ($q) => $q->where('severity', $request->string('severity')))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('inventory.alerts.index', ['alerts' => $alerts, 'filters' => $request->only(['alert_type', 'severity'])]);
    }

    public function refresh(Request $request): RedirectResponse
    {
        $this->alertService->refreshLowStockAlerts($request->integer('branch_id'));

        return back()->with('status', 'Inventory alerts refreshed.');
    }

    public function resolve(InventoryAlert $alert): RedirectResponse
    {
        $alert->update(['is_resolved' => true, 'resolved_at' => now()]);

        return back()->with('status', 'Alert marked as resolved.');
    }
}
