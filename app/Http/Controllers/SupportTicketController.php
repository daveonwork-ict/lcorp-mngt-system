<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Services\SupportTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function __construct(private readonly SupportTicketService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('admin.deployment.support.index', [
            'tickets' => $this->service->paginate($request->user(), $request->only(['status', 'priority'])),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'module_name' => ['required', 'string'],
            'issue_description' => ['required', 'string'],
            'priority' => ['required', 'string', 'in:critical,high,medium,low'],
        ]);

        $this->service->create($validated, $request->user());

        return back()->with('status', 'Support ticket created.');
    }

    public function update(Request $request, SupportTicket $supportTicket): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:open,in_progress,resolved,closed,cancelled'],
            'resolution_notes' => ['nullable', 'string'],
        ]);

        $this->service->update($supportTicket, $validated);

        return back()->with('status', 'Support ticket updated.');
    }
}
