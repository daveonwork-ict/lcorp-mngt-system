<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerNote;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerNoteController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function store(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'note' => ['required', 'string'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        $note = CustomerNote::query()->create([
            'customer_id' => $customer->id,
            'branch_id' => $validated['branch_id'] ?? null,
            'note' => $validated['note'],
            'created_by' => auth()->id(),
        ]);

        $this->auditLogService->record('warranty', 'customer_note_added', [], $note->toArray(), $note->branch_id, 'Customer note added');

        return back()->with('status', 'Customer note added.');
    }
}
