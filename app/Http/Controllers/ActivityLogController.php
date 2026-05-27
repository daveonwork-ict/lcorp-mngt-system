<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = ActivityLog::query()
            ->with(['user', 'branch'])
            ->when($request->filled('module'), fn ($q) => $q->where('module_name', $request->string('module')))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.security.activity-logs', [
            'logs' => $logs,
        ]);
    }

    public function loginHistory(): View
    {
        $logs = ActivityLog::query()
            ->with('user')
            ->whereIn('action_type', ['login', 'logout', 'login_failed'])
            ->orWhereIn('action', ['login', 'logout', 'login_failed'])
            ->latest('id')
            ->paginate(20);

        return view('admin.security.login-history', [
            'logs' => $logs,
        ]);
    }
}
