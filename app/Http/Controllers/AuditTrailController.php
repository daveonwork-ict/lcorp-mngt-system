<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AuditTrailController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index(Request $request): View
    {
        $logs = AuditLog::query()
            ->with(['user', 'branch'])
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->integer('user_id')))
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $request->integer('branch_id')))
            ->when($request->filled('module_name'), fn ($q) => $q->where('module_name', $request->string('module_name')))
            ->when($request->filled('action_type'), fn ($q) => $q->where('action_type', $request->string('action_type')))
            ->when($request->filled('reference'), fn ($q) => $q->where('audit_number', 'like', '%'.$request->string('reference').'%'))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->string('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->string('date_to')))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.security.audit-trail', [
            'logs' => $logs,
            'users' => User::query()->orderBy('full_name')->get(),
            'branches' => Branch::query()->where('is_active', true)->orderBy('name')->get(),
            'filters' => $request->all(),
        ]);
    }

    public function exportCsv(Request $request): Response
    {
        $rows = AuditLog::query()
            ->when($request->filled('module_name'), fn ($q) => $q->where('module_name', $request->string('module_name')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->string('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->string('date_to')))
            ->latest('id')
            ->limit(1000)
            ->get();

        $csv = implode(',', ['Audit #', 'Module', 'Action', 'User', 'Branch', 'Created At'])."\n";
        foreach ($rows as $row) {
            $csv .= implode(',', [
                $row->audit_number,
                str_replace(',', ' ', $row->module_name),
                str_replace(',', ' ', $row->action_type),
                $row->user_id,
                $row->branch_id,
                $row->created_at,
            ])."\n";
        }

        $this->auditLogService->record('security', 'audit_report_exported', [], ['count' => $rows->count()], $request->integer('branch_id') ?: null, 'Audit trail exported');

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="audit-trail.csv"',
        ]);
    }
}
