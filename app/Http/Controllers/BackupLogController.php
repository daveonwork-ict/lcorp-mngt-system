<?php

namespace App\Http\Controllers;

use App\Services\BackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BackupLogController extends Controller
{
    public function __construct(private readonly BackupService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('admin.security.backup-logs', [
            'logs' => $this->service->paginate($request->only(['status', 'backup_type'])),
            'filters' => $request->only(['status', 'backup_type']),
        ]);
    }

    public function run(Request $request): RedirectResponse
    {
        $this->service->runManual($request->string('remarks')->toString() ?: null);

        return back()->with('status', 'Backup readiness run completed.');
    }
}
