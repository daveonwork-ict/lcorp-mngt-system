<?php

namespace App\Http\Controllers;

use App\Models\SecurityAlert;
use App\Services\SecurityAlertService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SecurityAlertController extends Controller
{
    public function __construct(private readonly SecurityAlertService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('admin.security.alerts', [
            'alerts' => $this->service->paginate($request->only(['severity', 'alert_type', 'is_resolved'])),
            'filters' => $request->only(['severity', 'alert_type', 'is_resolved']),
        ]);
    }

    public function resolve(SecurityAlert $securityAlert): RedirectResponse
    {
        $this->service->resolve($securityAlert);

        return back()->with('status', 'Security alert resolved.');
    }
}
