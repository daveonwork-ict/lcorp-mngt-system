<?php

namespace App\Http\Controllers;

use App\Services\TrainingLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrainingLogController extends Controller
{
    public function __construct(private readonly TrainingLogService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('admin.deployment.training.index', [
            'logs' => $this->service->paginate($request->user()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'training_group' => ['required', 'string'],
            'session_title' => ['required', 'string'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'facilitator_id' => ['nullable', 'integer', 'exists:users,id'],
            'attendee_name' => ['required', 'string'],
            'attendee_role' => ['required', 'string'],
            'status' => ['required', 'string', 'in:scheduled,completed,cancelled'],
            'scheduled_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string'],
        ]);

        $this->service->create($validated);

        return back()->with('status', 'Training log saved.');
    }
}
