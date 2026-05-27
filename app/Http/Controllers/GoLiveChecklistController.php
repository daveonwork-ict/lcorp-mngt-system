<?php

namespace App\Http\Controllers;

use App\Models\GoLiveChecklist;
use App\Services\GoLiveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GoLiveChecklistController extends Controller
{
    public function __construct(private readonly GoLiveService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('admin.deployment.golive.index', [
            'items' => $this->service->paginate($request->integer('branch_id') ?: null),
        ]);
    }

    public function update(Request $request, GoLiveChecklist $goLiveChecklist): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,passed,failed'],
            'remarks' => ['nullable', 'string'],
        ]);

        $this->service->updateStatus($goLiveChecklist, $validated['status'], $validated['remarks'] ?? null);

        return back()->with('status', 'Go-live checklist updated.');
    }
}
