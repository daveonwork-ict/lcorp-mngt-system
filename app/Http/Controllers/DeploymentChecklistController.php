<?php

namespace App\Http\Controllers;

use App\Models\DeploymentLog;
use App\Services\DeploymentChecklistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeploymentChecklistController extends Controller
{
    public function __construct(private readonly DeploymentChecklistService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('admin.deployment.checklists.index', [
            'items' => $this->service->paginate(),
        ]);
    }

    public function update(Request $request, DeploymentLog $deploymentLog): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,passed,failed'],
            'remarks' => ['nullable', 'string'],
        ]);

        $this->service->updateStatus($deploymentLog, $validated['status'], $validated['remarks'] ?? null);

        return back()->with('status', 'Deployment checklist updated.');
    }
}
